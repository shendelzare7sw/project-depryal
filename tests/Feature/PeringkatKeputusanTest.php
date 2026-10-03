<?php

declare(strict_types=1);

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Enums\UserRole;
use App\Models\Keputusan;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;

test('peringkat mengikuti golden dataset dan dapat dilihat semua role', function () {
    ['periode' => $periode] = periodeGolden();

    foreach ([UserRole::Admin, UserRole::Operator, UserRole::Pimpinan] as $role) {
        $response = $this->actingAs(userWithRole($role))->get(route('peringkat.index', $periode))->assertOk();
        $baris = $response->viewData('baris');

        expect($baris->map(fn ($b) => $b['aset']->nama_barang)->all())->toBe(['Aset A3', 'Aset A1', 'Aset A4', 'Aset A2'])
            ->and($baris->map(fn ($b) => $b['hasil']->rekomendasi)->all())->toBe([TindakanAset::Pertahankan, TindakanAset::Perbaiki, TindakanAset::Perbaiki, TindakanAset::Hapus])
            ->and($response->viewData('ringkasan'))->toBe(['pertahankan' => 1, 'perbaiki' => 2, 'hapus' => 1]);
        $response->assertSee('0,4170');
    }
});

test('detail perhitungan menampilkan penyebut, normalisasi, dan Yi sesuai perhitungan manual', function () {
    ['periode' => $periode] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('peringkat.detail', $periode))
        ->assertOk()->assertSee('7,348469')->assertSee('5,477226')
        ->assertSee('0,5443')->assertSee('0,2177')->assertSee('0,2693')->assertSee('62,65');
});

test('detail aset menampilkan nilai, label rubrik, skor, dan rekomendasi', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Admin))->get(route('peringkat.show', [$periode, $aset['A1']]))
        ->assertOk()->assertSee('Aset A1')->assertSee('C1-label-4')->assertSee('62,65')->assertSee('Perbaiki')
        ->assertDontSee(route('keputusan.edit', [$periode, $aset['A1']]));
});

test('peringatan biaya tinggi muncul bila fungsi ≥ 4 dan biaya = skala maksimum', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();
    $biaya = Kriteria::firstWhere('kode', 'C3');
    NilaiKriteriaAset::where(['aset_id' => $aset['A1']->id, 'kriteria_id' => $biaya->id])->update(['nilai' => 5]);

    $baris = $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('peringkat.index', $periode))
        ->assertSee('Biaya tinggi — pertimbangkan hapus')->viewData('baris');

    expect($baris->firstWhere('aset.id', $aset['A1']->id)['peringatan'])->toBeTrue()
        ->and($baris->firstWhere('aset.id', $aset['A3']->id)['peringatan'])->toBeFalse();
});

test('pimpinan memutuskan sesuai rekomendasi tanpa catatan lalu diarahkan ke aset berikutnya', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();
    $pimpinan = userWithRole(UserRole::Pimpinan);

    $this->actingAs($pimpinan)->get(route('keputusan.edit', [$periode, $aset['A3']]))->assertOk()->assertSee('Rekomendasi');

    $this->actingAs($pimpinan)->put(route('keputusan.update', [$periode, $aset['A3']]), ['tindakan' => 'pertahankan'])
        ->assertRedirect(route('keputusan.edit', [$periode, $aset['A1']]))->assertSessionHas('success');

    $keputusan = Keputusan::sole();
    expect($keputusan->tindakan)->toBe(TindakanAset::Pertahankan)
        ->and($keputusan->rekomendasi_sistem)->toBe(TindakanAset::Pertahankan)
        ->and($keputusan->user_id)->toBe($pimpinan->id);
});

test('keputusan berbeda dari rekomendasi wajib catatan minimal 10 karakter', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();
    $pimpinan = userWithRole(UserRole::Pimpinan);

    $this->actingAs($pimpinan)->put(route('keputusan.update', [$periode, $aset['A2']]), ['tindakan' => 'perbaiki', 'catatan' => 'singkat'])
        ->assertSessionHasErrors('catatan');
    expect(Keputusan::count())->toBe(0);

    $this->actingAs($pimpinan)->put(route('keputusan.update', [$periode, $aset['A2']]), ['tindakan' => 'perbaiki', 'catatan' => 'Masih dipakai warga, cukup diperbaiki.'])
        ->assertSessionHasNoErrors();
    expect(Keputusan::sole()->tindakan)->toBe(TindakanAset::Perbaiki);
});

test('finalisasi ditolak bila masih ada aset belum diputuskan', function () {
    ['periode' => $periode] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Pimpinan))->post(route('periode.finalisasi', $periode))->assertSessionHas('error');

    expect($periode->refresh()->status)->toBe(StatusPeriode::Dihitung);
});

test('finalisasi mengunci periode, memperbarui status aset, dan memberi tahu operator', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();
    putuskanSemua($periode);
    $operator = userWithRole(UserRole::Operator);
    $pimpinan = userWithRole(UserRole::Pimpinan);

    $this->actingAs($pimpinan)->get(route('peringkat.index', $periode))->assertSee('Finalisasi Periode');
    $this->actingAs($pimpinan)->post(route('periode.finalisasi', $periode))
        ->assertRedirect(route('peringkat.index', $periode))->assertSessionHas('success');

    expect($periode->refresh()->status)->toBe(StatusPeriode::Final)
        ->and($periode->difinalisasi_oleh)->toBe($pimpinan->id)
        ->and($aset['A3']->refresh()->status)->toBe(StatusAset::Aktif)
        ->and($aset['A1']->refresh()->status)->toBe(StatusAset::DalamPerbaikan)
        ->and($aset['A2']->refresh()->status)->toBe(StatusAset::DiusulkanHapus)
        ->and($operator->notifications()->first()->data['judul'])->toBe('Periode difinalisasi');

    $this->actingAs($pimpinan)->put(route('keputusan.update', [$periode, $aset['A3']]), ['tindakan' => 'hapus', 'catatan' => 'Mencoba mengubah setelah final.'])
        ->assertSessionHas('error');
});

test('keputusan ditolak sebelum periode dihitung', function () {
    siapkanKriteria();
    $periode = periodeDraft(2);
    $aset = $periode->aset->first();

    $this->actingAs(userWithRole(UserRole::Pimpinan))->put(route('keputusan.update', [$periode, $aset]), ['tindakan' => 'pertahankan'])
        ->assertSessionHas('error');
});

test('operator dan admin tidak dapat memutuskan atau memfinalisasi', function (UserRole $role) {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();
    $user = userWithRole($role);

    $this->actingAs($user)->get(route('keputusan.edit', [$periode, $aset['A1']]))->assertForbidden();
    $this->actingAs($user)->put(route('keputusan.update', [$periode, $aset['A1']]), ['tindakan' => 'perbaiki'])->assertForbidden();
    $this->actingAs($user)->post(route('periode.finalisasi', $periode))->assertForbidden();
    $this->actingAs($user)->get(route('peringkat.index', $periode))->assertDontSee('Mulai Putuskan');
})->with([UserRole::Operator, UserRole::Admin]);

test('menu Hasil MOORA dan Keputusan mengarah ke periode yang tepat', function () {
    $pimpinan = userWithRole(UserRole::Pimpinan);
    $this->actingAs($pimpinan)->get(route('hasil.index'))->assertOk()->assertSee('Belum ada periode penilaian');

    ['periode' => $periode] = periodeGolden();

    $this->actingAs($pimpinan)->get(route('hasil.index'))->assertRedirect(route('peringkat.index', $periode));
    $this->actingAs($pimpinan)->get(route('keputusan.index'))->assertRedirect(route('peringkat.index', [$periode, 'keputusan' => 'belum']));
    $this->actingAs($pimpinan)->get(route('peringkat.index', [$periode, 'keputusan' => 'belum']))->assertViewHas('baris', fn ($b) => $b->count() === 4);
});
