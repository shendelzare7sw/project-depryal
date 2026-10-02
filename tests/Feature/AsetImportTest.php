<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Exports\AsetExport;
use App\Exports\AsetTemplateExport;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * Baris BMD (urutan kolom = AsetImport::HEADINGS).
 *
 * @return list<mixed>
 */
function barisBmd(array $override = []): array
{
    $baris = array_merge([
        'kategori' => 'Gedung Kantor Pemerintah',
        'kode_barang' => '1.03.01.01.001',
        'nup' => 1,
        'nama_barang' => 'Gedung Kantor Camat Batuceper',
        'jumlah' => 1,
        'luas' => '750,00',
        'tanggal_perolehan' => '15/01/2005',
        'harga_satuan' => '1.250.000.000,00',
        'nilai_perolehan' => '1.250.000.000,00',
        'umur_ekonomis' => 50,
        'akumulasi_penyusutan' => '525.000.000,00',
        'sisa_ueb' => 29,
        'nilai_buku' => '725.000.000,00',
        'lokasi' => 'Jl. Raya Batuceper No. 1',
    ], $override);

    return array_values($baris);
}

function berkasBmd(array $rows, string $name = 'bmd.xlsx'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, Excel::raw(new AsetTemplateExport($rows), ExcelWriter::XLSX));
}

function unggahBmd(User $user, array $rows): void
{
    test()->actingAs($user)->post(route('aset.import.store'), ['file' => berkasBmd($rows)])
        ->assertRedirect(route('aset.import.preview'));
}

test('operator mengunduh template Excel import BMD', function () {
    $this->actingAs(userWithRole(UserRole::Operator))->get(route('aset.import.template'))
        ->assertOk()->assertDownload('template-import-bmd.xlsx');
});

test('halaman import menampilkan form unggah dan tautan template', function () {
    $this->actingAs(userWithRole(UserRole::Operator))->get(route('aset.import'))
        ->assertOk()->assertSee(route('aset.import.template'))->assertSee('Unggah');
});

test('unggah menampilkan pratinjau baris valid dan error tanpa menyimpan data', function () {
    $operator = userWithRole(UserRole::Operator);

    unggahBmd($operator, [
        barisBmd(),
        barisBmd(['nama_barang' => '', 'nup' => 2]),
        barisBmd(['tanggal_perolehan' => '31/13/2020', 'nup' => 3]),
        barisBmd(['sisa_ueb' => 70, 'nup' => 4]),
        barisBmd(['nup' => 1, 'nama_barang' => 'Duplikat']),
    ]);

    $response = $this->actingAs($operator)->get(route('aset.import.preview'))->assertOk();

    $result = $response->viewData('result');
    expect($result->valid)->toHaveCount(1)
        ->and($result->invalid)->toHaveCount(4)
        ->and(collect($result->invalid)->pluck('baris')->all())->toBe([3, 4, 5, 6]);

    $response->assertSee('nama barang wajib diisi')
        ->assertSee('Tanggal perolehan tidak valid')->assertSee('sama dengan baris 2')
        ->assertSee(route('aset.import.confirm'));

    expect(Aset::count())->toBe(0);
});

test('konfirmasi menyimpan baris valid: angka & tanggal format Indonesia, kategori dibuat otomatis', function () {
    $operator = userWithRole(UserRole::Operator);
    unggahBmd($operator, [barisBmd(), barisBmd(['nama_barang' => '', 'nup' => 2])]);

    $this->actingAs($operator)->post(route('aset.import.confirm'))
        ->assertRedirect(route('aset.index'))->assertSessionHas('success', '1 data aset berhasil diimpor.');

    $aset = Aset::sole();
    expect((float) $aset->nilai_perolehan)->toBe(1_250_000_000.0)
        ->and((float) $aset->akumulasi_penyusutan)->toBe(525_000_000.0)
        ->and((float) $aset->luas)->toBe(750.0)
        ->and($aset->tanggal_perolehan->format('Y-m-d'))->toBe('2005-01-15')
        ->and($aset->kategori->nama)->toBe('Gedung Kantor Pemerintah');

    Storage::disk('local')->assertMissing("imports/aset-{$operator->id}.xlsx");
});

test('import selesai mengirim notifikasi ke admin dan operator lain, bukan ke pengimpor', function () {
    $operator = userWithRole(UserRole::Operator);
    $operatorLain = userWithRole(UserRole::Operator);
    $admin = userWithRole(UserRole::Admin);
    $pimpinan = userWithRole(UserRole::Pimpinan);
    unggahBmd($operator, [barisBmd()]);

    $this->actingAs($operator)->post(route('aset.import.confirm'));

    expect($admin->notifications()->count())->toBe(1)
        ->and($operatorLain->notifications()->count())->toBe(1)
        ->and($operator->notifications()->count())->toBe(0)
        ->and($pimpinan->notifications()->count())->toBe(0)
        ->and($admin->notifications()->first()->data['pesan'])->toContain('1 data aset diimpor oleh');
});

test('import upsert berdasarkan kode barang + NUP dan memakai kategori yang sudah ada', function () {
    $operator = userWithRole(UserRole::Operator);
    $kategori = KategoriAset::factory()->create(['nama' => 'Gedung Kantor Pemerintah']);
    Aset::factory()->create(['kode_barang' => '1.03.01.01.001', 'nup' => 1, 'nama_barang' => 'Nama Lama']);

    unggahBmd($operator, [barisBmd(['nama_barang' => 'Nama Baru']), barisBmd(['nup' => 2])]);
    $this->actingAs($operator)->post(route('aset.import.confirm'))->assertSessionHas('success');

    expect(Aset::count())->toBe(2)
        ->and(Aset::where('nup', 1)->value('nama_barang'))->toBe('Nama Baru')
        ->and(KategoriAset::count())->toBe(2) // kategori factory aset lama + kategori yang dicocokkan
        ->and(Aset::where('nup', 2)->value('kategori_aset_id'))->toBe($kategori->id);
});

test('NUP kosong diisi berurutan per kode barang', function () {
    $operator = userWithRole(UserRole::Operator);
    unggahBmd($operator, [
        barisBmd(['nup' => null, 'nama_barang' => 'Tugu A']),
        barisBmd(['nup' => 1, 'nama_barang' => 'Tugu B']),
        barisBmd(['nup' => null, 'nama_barang' => 'Tugu C']),
        barisBmd(['nup' => null, 'kode_barang' => '1.03.01.02.001', 'nama_barang' => 'Pos D']),
    ]);

    $this->actingAs($operator)->post(route('aset.import.confirm'))->assertSessionHas('success');

    expect(Aset::where('nama_barang', 'Tugu B')->value('nup'))->toBe(1)
        ->and(Aset::where('nama_barang', 'Tugu A')->value('nup'))->toBe(2)
        ->and(Aset::where('nama_barang', 'Tugu C')->value('nup'))->toBe(3)
        ->and(Aset::where('nama_barang', 'Pos D')->value('nup'))->toBe(1);
});

test('kolom opsional terisi otomatis dan tahun saja diterima sebagai tanggal', function () {
    $operator = userWithRole(UserRole::Operator);
    unggahBmd($operator, [barisBmd([
        'jumlah' => null, 'harga_satuan' => null, 'akumulasi_penyusutan' => null, 'nilai_buku' => null,
        'tanggal_perolehan' => 2010, 'nilai_perolehan' => 300000000,
    ])]);

    $this->actingAs($operator)->post(route('aset.import.confirm'));

    $aset = Aset::sole();
    expect($aset->jumlah)->toBe(1)
        ->and((float) $aset->harga_satuan)->toBe(300_000_000.0)
        ->and((float) $aset->nilai_buku)->toBe(300_000_000.0)
        ->and($aset->tanggal_perolehan->format('Y-m-d'))->toBe('2010-01-01');
});

test('import 96 baris selesai kurang dari 10 detik', function () {
    $operator = userWithRole(UserRole::Operator);
    $rows = array_map(fn (int $i) => barisBmd([
        'kode_barang' => '1.03.01.'.str_pad((string) intdiv($i, 10), 2, '0', STR_PAD_LEFT).'.001',
        'nup' => $i % 10 + 1,
        'nama_barang' => "Gedung Uji {$i}",
        'kategori' => 'Kategori '.($i % 4),
    ]), range(0, 95));

    $mulai = microtime(true);
    unggahBmd($operator, $rows);
    $this->actingAs($operator)->get(route('aset.import.preview'))->assertOk();
    $this->actingAs($operator)->post(route('aset.import.confirm'))->assertSessionHas('success', '96 data aset berhasil diimpor.');

    expect(microtime(true) - $mulai)->toBeLessThan(10.0)
        ->and(Aset::count())->toBe(96)
        ->and(KategoriAset::count())->toBe(4);
});

test('berkas bukan Excel atau lebih dari 12 MB ditolak', function () {
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->post(route('aset.import.store'), ['file' => UploadedFile::fake()->create('data.pdf', 10)])
        ->assertSessionHasErrors('file');
    $this->actingAs($operator)->post(route('aset.import.store'), ['file' => UploadedFile::fake()->create('besar.xlsx', 12289)])
        ->assertSessionHasErrors('file');
});

test('berkas tanpa judul kolom BMD ditolak dengan pesan', function () {
    $operator = userWithRole(UserRole::Operator);
    $file = UploadedFile::fake()->createWithContent('acak.xlsx', Excel::raw(new class implements FromArray
    {
        public function array(): array
        {
            return [['Foo', 'Bar'], ['1', '2']];
        }
    }, ExcelWriter::XLSX));

    $this->actingAs($operator)->post(route('aset.import.store'), ['file' => $file]);
    $this->actingAs($operator)->from(route('aset.import'))->get(route('aset.import.preview'))
        ->assertRedirect(route('aset.import'))->assertSessionHas('error');
});

test('pratinjau tanpa berkas yang diunggah diarahkan kembali dengan pesan', function () {
    $this->actingAs(userWithRole(UserRole::Operator))->from(route('aset.import'))->get(route('aset.import.preview'))
        ->assertRedirect(route('aset.import'))->assertSessionHas('error');
});

test('admin dan pimpinan tidak bisa import BMD', function (UserRole $role) {
    $user = userWithRole($role);

    $this->actingAs($user)->get(route('aset.import'))->assertForbidden();
    $this->actingAs($user)->get(route('aset.import.template'))->assertForbidden();
    $this->actingAs($user)->post(route('aset.import.store'), ['file' => berkasBmd([barisBmd()])])->assertForbidden();
    $this->actingAs($user)->post(route('aset.import.confirm'))->assertForbidden();
})->with([UserRole::Admin, UserRole::Pimpinan]);

test('hasil export dapat diimpor ulang tanpa duplikasi', function () {
    $operator = userWithRole(UserRole::Operator);
    Aset::factory()->count(3)->create();

    $raw = Excel::raw(new AsetExport, ExcelWriter::XLSX);
    $this->actingAs($operator)->post(route('aset.import.store'), ['file' => UploadedFile::fake()->createWithContent('export.xlsx', $raw)]);
    $this->actingAs($operator)->post(route('aset.import.confirm'))->assertSessionHas('success', '3 data aset berhasil diimpor.');

    expect(Aset::count())->toBe(3);
});
