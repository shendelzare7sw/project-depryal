<?php

declare(strict_types=1);

use App\Enums\StatusAset;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\AsetFoto;
use App\Models\KategoriAset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function dataAsetValid(array $override = []): array
{
    return array_merge([
        'kategori_aset_id' => KategoriAset::factory()->create()->id,
        'kode_barang' => '1.03.01.01.001',
        'nup' => 1,
        'nama_barang' => 'Gedung Kantor Camat Batuceper',
        'jumlah' => 1,
        'luas' => 750,
        'tanggal_perolehan' => '2005-01-15',
        'harga_satuan' => 1_250_000_000,
        'nilai_perolehan' => 1_250_000_000,
        'umur_ekonomis' => 50,
        'akumulasi_penyusutan' => 525_000_000,
        'sisa_ueb' => 29,
        'nilai_buku' => 725_000_000,
        'lokasi' => 'Jl. Raya Batuceper No. 1',
        'status' => StatusAset::Aktif->value,
    ], $override);
}

test('operator menambah aset beserta foto kamera/unggah', function () {
    Storage::fake('public');

    $response = $this->actingAs(userWithRole(UserRole::Operator))->post(route('aset.store'), dataAsetValid([
        'fotos' => [UploadedFile::fake()->image('depan.jpg'), UploadedFile::fake()->image('samping.jpg')],
    ]));

    $aset = Aset::firstWhere('kode_barang', '1.03.01.01.001');
    $response->assertRedirect(route('aset.show', $aset))->assertSessionHas('success');
    expect($aset->fotos)->toHaveCount(2)
        ->and((float) $aset->nilai_buku)->toBe(725_000_000.0);
    Storage::disk('public')->assertExists($aset->fotos->first()->path);
});

test('validasi aset: kode barang + NUP unik, sisa UEB tidak melebihi UEB, foto maks 4 MB', function () {
    Storage::fake('public');
    $operator = userWithRole(UserRole::Operator);
    Aset::factory()->create(['kode_barang' => '1.03.01.01.001', 'nup' => 1]);

    $this->actingAs($operator)->post(route('aset.store'), dataAsetValid([
        'sisa_ueb' => 60,
        'fotos' => [UploadedFile::fake()->image('besar.jpg')->size(5000)],
    ]))->assertSessionHasErrors(['nup', 'sisa_ueb', 'fotos.0']);

    expect(Aset::count())->toBe(1);
});

test('kode barang sama dengan NUP berbeda diperbolehkan', function () {
    Aset::factory()->create(['kode_barang' => '1.03.01.01.001', 'nup' => 1]);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('aset.store'), dataAsetValid(['nup' => 2]))
        ->assertSessionHasNoErrors();

    expect(Aset::where('kode_barang', '1.03.01.01.001')->count())->toBe(2);
});

test('operator mengubah aset dan menambah foto baru', function () {
    Storage::fake('public');
    $aset = Aset::factory()->create();

    $this->actingAs(userWithRole(UserRole::Operator))->put(route('aset.update', $aset), dataAsetValid([
        'kode_barang' => $aset->kode_barang,
        'nup' => $aset->nup,
        'nama_barang' => 'Nama Baru',
        'status' => StatusAset::DalamPerbaikan->value,
        'fotos' => [UploadedFile::fake()->image('baru.jpg')],
    ]))->assertRedirect(route('aset.show', $aset));

    $aset->refresh();
    expect($aset->nama_barang)->toBe('Nama Baru')
        ->and($aset->status)->toBe(StatusAset::DalamPerbaikan)
        ->and($aset->fotos()->count())->toBe(1);
});

test('index mendukung pencarian, filter kategori/status, dan badge perlu perhatian', function () {
    $kategori = KategoriAset::factory()->create(['nama' => 'Posyandu']);
    Aset::factory()->create(['nama_barang' => 'Posyandu Mawar', 'kategori_aset_id' => $kategori->id, 'sisa_ueb' => 2]);
    Aset::factory()->create(['nama_barang' => 'Gedung Arsip', 'status' => StatusAset::DalamPerbaikan, 'sisa_ueb' => 20]);
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->get(route('aset.index', ['q' => 'Mawar']))
        ->assertOk()->assertSee('Posyandu Mawar')->assertDontSee('Gedung Arsip')->assertSee('Perlu perhatian');

    $this->actingAs($operator)->get(route('aset.index', ['kategori' => $kategori->id]))
        ->assertSee('Posyandu Mawar')->assertDontSee('Gedung Arsip');

    $this->actingAs($operator)->get(route('aset.index', ['status' => 'dalam_perbaikan']))
        ->assertSee('Gedung Arsip')->assertDontSee('Posyandu Mawar');
});

test('index dipaginasi 15 per halaman', function () {
    Aset::factory()->count(20)->create();

    $this->actingAs(userWithRole(UserRole::Operator))->get(route('aset.index'))
        ->assertOk()->assertSee('20 aset tercatat')->assertViewHas('aset', fn ($p) => $p->count() === 15);
});

test('detail aset menampilkan data BMD dan peringatan sisa UEB', function () {
    $aset = Aset::factory()->create(['nama_barang' => 'Pos Jaga Poris', 'sisa_ueb' => 3]);

    $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('aset.show', $aset))
        ->assertOk()->assertSee('Pos Jaga Poris')->assertSee($aset->kode_barang)->assertSee('Perlu perhatian')
        ->assertDontSee(route('aset.edit', $aset));
});

test('hapus aset = soft delete', function () {
    $aset = Aset::factory()->create();

    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('aset.destroy', $aset))
        ->assertRedirect(route('aset.index'))->assertSessionHas('success');

    $this->assertSoftDeleted($aset);
});

test('hapus aset ditolak bila aset ada di periode final', function () {
    $aset = Aset::factory()->create();
    periodeFinalDengan($aset);

    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('aset.destroy', $aset))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($aset);
});

test('operator menghapus foto aset', function () {
    Storage::fake('public');
    $aset = Aset::factory()->create();
    $path = UploadedFile::fake()->image('a.jpg')->store("aset/{$aset->id}", 'public');
    $foto = $aset->fotos()->create(['path' => $path]);

    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('aset.foto.destroy', [$aset, $foto]))
        ->assertRedirect(route('aset.show', $aset));

    expect(AsetFoto::find($foto->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('admin dan pimpinan hanya bisa membaca data aset', function (UserRole $role) {
    $user = userWithRole($role);
    $aset = Aset::factory()->create();

    $this->actingAs($user)->get(route('aset.index'))->assertOk()->assertDontSee(route('aset.create'));
    $this->actingAs($user)->get(route('aset.show', $aset))->assertOk();
    $this->actingAs($user)->get(route('aset.create'))->assertForbidden();
    $this->actingAs($user)->post(route('aset.store'), dataAsetValid())->assertForbidden();
    $this->actingAs($user)->get(route('aset.edit', $aset))->assertForbidden();
    $this->actingAs($user)->put(route('aset.update', $aset), dataAsetValid())->assertForbidden();
    $this->actingAs($user)->delete(route('aset.destroy', $aset))->assertForbidden();
    $this->actingAs($user)->get(route('aset.export'))->assertForbidden();
})->with([UserRole::Admin, UserRole::Pimpinan]);

test('operator melihat tombol tambah, import, export dan form 2 tahap', function () {
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->get(route('aset.index'))
        ->assertSee(route('aset.create'))->assertSee(route('aset.import'))->assertSee(route('aset.export'));

    $this->actingAs($operator)->get(route('aset.create'))
        ->assertOk()->assertSee('Data BMD')->assertSee('Kondisi &amp; Foto', false)->assertSee('capture="environment"', false);
});

test('operator mengekspor daftar aset ke Excel', function () {
    Aset::factory()->count(3)->create();

    $this->actingAs(userWithRole(UserRole::Operator))->get(route('aset.export'))
        ->assertOk()->assertDownload('data-aset-bmd-'.now()->format('Ymd').'.xlsx');
});

test('kategori aset: CRUD operator, hapus ditolak bila masih dipakai', function () {
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->post(route('kategori-aset.store'), ['kode' => 'GB-BRU', 'nama' => 'Gedung Baru'])
        ->assertRedirect(route('kategori-aset.index'));
    $kategori = KategoriAset::firstWhere('kode', 'GB-BRU');

    $this->actingAs($operator)->put(route('kategori-aset.update', $kategori), ['kode' => 'GB-BRU', 'nama' => 'Gedung Lama'])
        ->assertSessionHasNoErrors();
    expect($kategori->refresh()->nama)->toBe('Gedung Lama');

    $this->actingAs($operator)->get(route('kategori-aset.index'))->assertOk()->assertSee('Gedung Lama');

    Aset::factory()->create(['kategori_aset_id' => $kategori->id]);
    $this->actingAs($operator)->delete(route('kategori-aset.destroy', $kategori))->assertSessionHas('error');
    expect(KategoriAset::find($kategori->id))->not->toBeNull();

    $kosong = KategoriAset::factory()->create();
    $this->actingAs($operator)->delete(route('kategori-aset.destroy', $kosong))->assertSessionHas('success');
    expect(KategoriAset::find($kosong->id))->toBeNull();
});

test('kategori aset hanya untuk operator', function (UserRole $role) {
    $this->actingAs(userWithRole($role))->get(route('kategori-aset.index'))->assertForbidden();
})->with([UserRole::Admin, UserRole::Pimpinan]);
