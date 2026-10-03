<?php

declare(strict_types=1);

use App\Actions\Periode\HitungPeriode;
use App\Enums\FormatLaporan;
use App\Enums\JenisLaporan;
use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\Kriteria;
use App\Models\Laporan;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * Periode dihitung berisi $n aset (nilai bervariasi) di atas kriteria siapkanKriteria().
 */
function periodeDihitung(int $n): PeriodePenilaian
{
    $kriteria = siapkanKriteria();
    $periode = PeriodePenilaian::factory()->create(['status' => StatusPeriode::Dinilai]);

    foreach (Aset::factory()->count($n)->create() as $i => $aset) {
        $periode->aset()->attach($aset->id);
        foreach ($kriteria as $j => $k) {
            NilaiKriteriaAset::create(['periode_id' => $periode->id, 'aset_id' => $aset->id, 'kriteria_id' => $k->id, 'nilai' => ($i * 7 + $j * 3) % 5 + 1]);
        }
    }

    app(HitungPeriode::class)->execute($periode, userWithRole(UserRole::Operator));

    return $periode->refresh();
}

test('operator membuat laporan PDF lengkap: berkas tersimpan, riwayat tercatat, unduhan otomatis', function () {
    ['periode' => $periode] = periodeGolden();
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'lengkap', 'format' => 'pdf'])
        ->assertRedirect(route('laporan.index'))->assertSessionHas('success')->assertSessionHas('unduh');

    $laporan = Laporan::sole();
    expect($laporan->jenis)->toBe(JenisLaporan::Lengkap)
        ->and($laporan->format)->toBe(FormatLaporan::Pdf)
        ->and($laporan->user_id)->toBe($operator->id)
        ->and(Storage::disk('local')->get($laporan->path))->toStartWith('%PDF');

    $this->actingAs(userWithRole(UserRole::Admin))->get(route('laporan.download', $laporan))
        ->assertOk()->assertDownload($laporan->nama_file);
});

test('laporan Excel berisi sheet sesuai jenis', function (string $jenis, array $sheets) {
    ['periode' => $periode] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Pimpinan))
        ->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => $jenis, 'format' => 'xlsx'])->assertSessionHas('success');

    $berkas = Storage::disk('local')->path(Laporan::sole()->path);
    $buku = IOFactory::load($berkas);

    expect($buku->getSheetNames())->toBe($sheets)
        ->and($buku->getSheetByName('Peringkat')?->getCell('D2')->getValue() ?? $buku->getSheet(0)->getCell('D2')->getValue())->toBe('Aset A3');
})->with([
    'peringkat' => ['peringkat', ['Peringkat']],
    'keputusan' => ['keputusan', ['Keputusan']],
    'lengkap' => ['lengkap', ['Kriteria', 'Matriks Nilai', 'Peringkat', 'Keputusan']],
]);

test('laporan PDF 96 aset berhasil dibuat (tabel multi-halaman)', function () {
    $periode = periodeDihitung(96);
    $mulai = microtime(true);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'peringkat', 'format' => 'pdf'])->assertSessionHas('success');

    $isi = Storage::disk('local')->get(Laporan::sole()->path);
    expect($isi)->toStartWith('%PDF')
        ->and(preg_match_all('/\/Type\s*\/Page[^s]/', $isi))->toBeGreaterThan(1)
        ->and(microtime(true) - $mulai)->toBeLessThan(20.0);
});

test('laporan hanya untuk periode yang sudah dihitung atau final', function () {
    siapkanKriteria();
    $periode = periodeDraft(2);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'peringkat', 'format' => 'pdf'])
        ->assertSessionHasErrors('periode_id');
    expect(Laporan::count())->toBe(0);
});

test('admin hanya dapat melihat & mengunduh laporan, tidak membuat', function () {
    ['periode' => $periode] = periodeGolden();
    $admin = userWithRole(UserRole::Admin);

    $this->actingAs($admin)->get(route('laporan.index'))->assertOk()->assertDontSee('Buat &amp; Unduh', false);
    $this->actingAs($admin)->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'peringkat', 'format' => 'pdf'])->assertForbidden();
});

test('unduh berkas yang hilang memberi pesan', function () {
    $laporan = Laporan::factory()->create(['path' => 'laporan/tidak-ada.pdf']);

    $this->actingAs(userWithRole(UserRole::Operator))->from(route('laporan.index'))
        ->get(route('laporan.download', $laporan))->assertRedirect(route('laporan.index'))->assertSessionHas('error');
});

test('dashboard operator menampilkan aksi kontekstual sesuai alur', function () {
    $operator = userWithRole(UserRole::Operator);
    $this->actingAs($operator)->get(route('dashboard'))->assertOk()->assertSee('Buat Periode Penilaian');

    siapkanKriteria();
    $periode = periodeDraft(2);
    $this->actingAs($operator)->get(route('dashboard'))->assertSee('Lanjutkan Penilaian')->assertSee($periode->nama);
});

test('dashboard pimpinan menampilkan aset menunggu keputusan, distribusi, dan prioritas', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('dashboard'))
        ->assertOk()->assertSee('4 aset menunggu keputusan')->assertSee('Distribusi rekomendasi sistem')
        ->assertSee('Mulai Putuskan')->assertSee(route('keputusan.edit', [$periode, $aset['A2']]))
        ->assertViewHas('prioritas', fn ($p) => $p->first()['aset']->is($aset['A2']));
});

test('dashboard admin menampilkan statistik pengguna per role dan ambang', function () {
    $this->actingAs(userWithRole(UserRole::Admin))->get(route('dashboard'))
        ->assertOk()->assertSee('Pengguna per role')->assertSee('Ambang rekomendasi')->assertSee('66.67');
});

test('jumlah query dashboard pimpinan & peringkat tidak bertambah seiring jumlah aset (tanpa N+1)', function () {
    $pimpinan = userWithRole(UserRole::Pimpinan);
    $hitung = function (string $url) use ($pimpinan): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($pimpinan)->get($url)->assertOk();

        return count(DB::getQueryLog());
    };

    $kecil = periodeDihitung(3);
    $q1 = [$hitung(route('dashboard')), $hitung(route('peringkat.index', $kecil))];
    PeriodePenilaian::query()->update(['status' => StatusPeriode::Final]);
    Kriteria::query()->delete();

    $besar = periodeDihitung(30);
    $q2 = [$hitung(route('dashboard')), $hitung(route('peringkat.index', $besar))];

    expect($q2)->toBe($q1);
});
