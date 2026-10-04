<?php

declare(strict_types=1);

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\KategoriAset;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;

test('operator menghapus periode draft beserta nilainya', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft();
    isiSemuaNilai($periode, $kriteria);

    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('periode.destroy', $periode))
        ->assertRedirect(route('periode.index'));

    expect(PeriodePenilaian::count())->toBe(0)
        ->and(NilaiKriteriaAset::count())->toBe(0)
        ->and(Aset::count())->toBe(3);
});

test('periode yang sudah dihitung atau final tidak dapat dihapus', function (StatusPeriode $status) {
    $periode = periodeDraft();
    $periode->update(['status' => $status]);

    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('periode.destroy', $periode))
        ->assertSessionHas('error');

    expect($periode->fresh())->not->toBeNull();
})->with([StatusPeriode::Dihitung, StatusPeriode::Final]);

test('hanya operator yang dapat menghapus periode dan mengelola aset periode', function (UserRole $role) {
    $periode = periodeDraft();
    $aset = $periode->aset()->first();
    $user = userWithRole($role);

    $this->actingAs($user)->delete(route('periode.destroy', $periode))->assertForbidden();
    $this->actingAs($user)->get(route('periode.aset.create', $periode))->assertForbidden();
    $this->actingAs($user)->delete(route('periode.aset.destroy', [$periode, $aset]))->assertForbidden();
})->with([UserRole::Admin, UserRole::Pimpinan]);

test('menambah aset aktif ke periode menurunkan status dinilai menjadi draft', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft();
    isiSemuaNilai($periode, $kriteria);
    $baru = Aset::factory()->create();
    $nonaktif = Aset::factory()->create(['status' => StatusAset::DiusulkanHapus]);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->get(route('periode.aset.create', $periode))->assertOk()->assertSee($baru->nama_barang)->assertDontSee($nonaktif->nama_barang);

    $this->post(route('periode.aset.store', $periode), ['aset_ids' => [$baru->id, $nonaktif->id, $periode->aset()->first()->id]])
        ->assertRedirect(route('periode.show', $periode))
        ->assertSessionHas('success', '1 aset ditambahkan ke periode. Lengkapi nilainya.');

    expect($periode->aset()->count())->toBe(4)
        ->and($periode->fresh()->status)->toBe(StatusPeriode::Draft);
});

test('mengubah daftar aset periode yang sudah dihitung menghapus hasil MOORA', function () {
    ['periode' => $periode] = periodeGolden();
    $aset = $periode->aset()->first();

    $this->actingAs(userWithRole(UserRole::Operator))->delete(route('periode.aset.destroy', [$periode, $aset]))
        ->assertSessionHas('success');

    expect($periode->aset()->whereKey($aset->id)->exists())->toBeFalse()
        ->and(NilaiKriteriaAset::where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->exists())->toBeFalse()
        ->and(HasilMoora::where('periode_id', $periode->id)->exists())->toBeFalse()
        ->and($periode->fresh()->status)->toBe(StatusPeriode::Dinilai);
});

test('periode harus tetap berisi minimal 2 aset dan aset periode final terkunci', function () {
    $periode = periodeDraft(2);
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->delete(route('periode.aset.destroy', [$periode, $periode->aset()->first()]))
        ->assertSessionHas('error');
    expect($periode->aset()->count())->toBe(2);

    $periode->update(['status' => StatusPeriode::Final]);
    $this->post(route('periode.aset.store', $periode), ['aset_ids' => [Aset::factory()->create()->id]])->assertSessionHas('error');
    expect($periode->aset()->count())->toBe(2);
});

test('aset baru dapat langsung dimasukkan ke periode berjalan', function () {
    $periode = periodeDraft();
    $kategori = KategoriAset::factory()->create();
    $data = Aset::factory()->make(['kategori_aset_id' => $kategori->id])->only([
        'kategori_aset_id', 'kode_barang', 'nup', 'nama_barang', 'jumlah', 'luas', 'harga_satuan', 'nilai_perolehan',
        'umur_ekonomis', 'akumulasi_penyusutan', 'sisa_ueb', 'nilai_buku', 'lokasi',
    ]) + ['tanggal_perolehan' => '2020-01-01', 'status' => StatusAset::Aktif->value];

    $this->actingAs(userWithRole(UserRole::Operator))->get(route('aset.create'))->assertSee('Ikut periode berjalan');
    $this->post(route('aset.store'), $data + ['masuk_periode' => '1'])->assertSessionHasNoErrors();
    $this->post(route('aset.store'), ['nup' => $data['nup'] + 1, 'masuk_periode' => '0'] + $data)->assertSessionHasNoErrors();

    expect($periode->aset()->count())->toBe(4);
});
