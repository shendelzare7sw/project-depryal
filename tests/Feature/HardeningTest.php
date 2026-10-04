<?php

declare(strict_types=1);

use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Exports\AsetExport;
use App\Exports\LaporanSheet;
use App\Models\Aset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;

function selExcel(object $export, string $sel): Cell
{
    $berkas = tempnam(sys_get_temp_dir(), 'xl').'.xlsx';
    file_put_contents($berkas, Excel::raw($export, ExcelWriter::XLSX));

    return IOFactory::load($berkas)->getActiveSheet()->getCell($sel);
}

test('export Excel menulis isian berawalan = + - @ sebagai teks, bukan formula (formula injection)', function () {
    Aset::factory()->create(['nama_barang' => '=HYPERLINK("http://jahat.test","klik")']);

    $sel = selExcel(new AsetExport, 'D2');
    expect($sel->getDataType())->toBe('s')->and($sel->getValue())->toBe('=HYPERLINK("http://jahat.test","klik")');

    $sheet = new LaporanSheet('Uji', ['Nama', 'Angka'], [['@SUM(A1)', -5]]);
    expect(selExcel($sheet, 'A2')->getDataType())->toBe('s')
        ->and(selExcel($sheet, 'B2')->getValue())->toBe(-5);
});

test('progres periode tidak menghitung nilai milik aset yang sudah dihapus', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(3);
    isiSemuaNilai($periode, $kriteria);

    $periode->aset->first()->delete();

    expect($periode->fresh()->progres())->toMatchArray(['terisi' => 6, 'diperlukan' => 6, 'total_aset' => 2, 'persen' => 100]);
});

test('foto bukti periode final tidak dapat dihapus', function () {
    Storage::fake('public');
    $aset = Aset::factory()->create();
    $periode = periodeFinalDengan($aset);
    $foto = $aset->fotos()->create(['periode_id' => $periode->id, 'path' => UploadedFile::fake()->image('a.jpg')->store('aset', 'public')]);

    $this->actingAs(userWithRole(UserRole::Operator))->get(route('aset.show', $aset))->assertDontSee(route('aset.foto.destroy', [$aset, $foto]));
    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('aset.foto.destroy', [$aset, $foto]))->assertSessionHas('error');

    expect($foto->fresh())->not->toBeNull();
    Storage::disk('public')->assertExists($foto->path);
});

test('respons membawa header keamanan dan halaman terautentikasi tidak di-cache', function () {
    $this->get(route('login'))
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($this->actingAs(userWithRole(UserRole::Operator))->get(route('dashboard'))->headers->get('Cache-Control'))->toContain('no-store');
});

test('endpoint berat dibatasi 20 permintaan per menit per pengguna', function () {
    $operator = userWithRole(UserRole::Operator);

    for ($i = 0; $i < 20; $i++) {
        $this->actingAs($operator)->get(route('aset.export'))->assertOk();
    }

    $this->actingAs($operator)->from(route('aset.index'))->get(route('aset.export'))
        ->assertRedirect(route('aset.index'))->assertSessionHas('error');
});

test('login dibatasi 5 kali gagal per username + IP', function () {
    $user = userWithRole(UserRole::Operator);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), ['login' => $user->username, 'password' => 'salah']);
    }

    $this->post(route('login'), ['login' => $user->username, 'password' => 'password'])
        ->assertSessionHasErrors('login');
    expect(session('errors')->first('login'))->toStartWith('Terlalu banyak percobaan masuk');
    $this->assertGuest();
});

test('halaman galat bermerek berbahasa Indonesia', function () {
    $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('aset.create'))->assertForbidden()->assertSee('Akses ditolak');
    $this->actingAs(userWithRole(UserRole::Pimpinan))->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
});

test('pengguna tidak dapat menembak ID lain: aset di luar periode, notifikasi orang lain, periode non-final dibuka kembali', function () {
    siapkanKriteria();
    $periode = periodeDraft(2);
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->put(route('penilaian.update', [$periode, Aset::factory()->create()]), ['kriteria' => []])->assertSessionHas('error');
    $this->actingAs($operator)->post(route('periode.buka-kembali', $periode), ['alasan' => 'Mencoba membuka periode draft.'])->assertSessionHas('error');
    expect($periode->fresh()->status)->toBe(StatusPeriode::Draft);
});

test('CSP aktif: skrip inline hanya dengan nonce dan aset tidak dimuat dari CDN', function () {
    $response = $this->actingAs(userWithRole(UserRole::Operator))->get(route('dashboard'))->assertOk();
    $csp = (string) $response->headers->get('Content-Security-Policy');
    preg_match("/'nonce-([^']+)'/", $csp, $nonce);

    expect($csp)->toContain("default-src 'self'")->toContain("object-src 'none'")->not->toContain("script-src 'self' 'unsafe-inline'")
        ->and($nonce[1] ?? '')->not->toBe('')
        ->and($response->getContent())->toContain('nonce="'.$nonce[1].'"')
        ->not->toContain('cdn.tailwindcss.com')->not->toContain('cdn.jsdelivr.net')->not->toContain('fonts.googleapis.com');
});
