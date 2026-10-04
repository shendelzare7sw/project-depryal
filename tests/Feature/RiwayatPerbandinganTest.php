<?php

declare(strict_types=1);

use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Enums\UserRole;
use App\Models\HasilMoora;
use App\Models\PeriodePenilaian;

/**
 * Periode lama tiruan: A1 peringkat 1, A2 peringkat 2 (Pertahankan), A3 peringkat 3 — A4 belum dinilai.
 */
function periodeLamaDari(array $aset): PeriodePenilaian
{
    $lama = PeriodePenilaian::factory()->create(['status' => StatusPeriode::Final, 'nama' => 'Penilaian 2025', 'tanggal_mulai' => '2025-01-06']);
    foreach (['A1' => 1, 'A2' => 2, 'A3' => 3] as $kode => $ranking) {
        $lama->aset()->attach($aset[$kode]->id);
        HasilMoora::create(['periode_id' => $lama->id, 'aset_id' => $aset[$kode]->id, 'yi' => 0.1, 'skor_relatif' => 100 - $ranking * 30,
            'ranking' => $ranking, 'rekomendasi' => TindakanAset::Pertahankan, 'detail' => []]);
    }

    return $lama;
}

test('detail aset menampilkan riwayat hasil MOORA dan keputusan per periode', function () {
    ['periode' => $periode, 'aset' => $aset] = periodeGolden();
    $periode->update(['tanggal_mulai' => '2026-10-01']);
    periodeLamaDari($aset);
    putuskanSemua($periode);

    $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('aset.show', $aset['A3']))
        ->assertOk()->assertSee('Riwayat penilaian')->assertSee($periode->nama)->assertSee('Penilaian 2025')
        ->assertSeeInOrder([$periode->nama, 'Penilaian 2025']);

    $this->get(route('aset.show', $aset['A4']))->assertSee($periode->nama)->assertDontSee('Penilaian 2025');
});

test('perbandingan periode menghitung naik, turun, aset baru, dan rekomendasi berubah', function () {
    ['periode' => $baru, 'aset' => $aset] = periodeGolden();
    $baru->update(['tanggal_mulai' => '2026-10-01']);
    $lama = periodeLamaDari($aset);
    $rankingBaru = $baru->hasilMoora->pluck('ranking', 'aset_id');

    $response = $this->actingAs(userWithRole(UserRole::Operator))->get(route('hasil.perbandingan'))
        ->assertOk()->assertSee($lama->nama.' → '.$baru->nama);

    $data = $response->viewData('data');
    $a3 = $data['baris']->first(fn ($b) => $b['aset']->is($aset['A3']));
    expect($a3['naik'])->toBe(3 - $rankingBaru[$aset['A3']->id])
        ->and($data['ringkasan']['baru'])->toBe(1)
        ->and($data['ringkasan']['keluar'])->toBe(0)
        ->and($data['ringkasan']['rekomendasi_berubah'])->toBe($baru->hasilMoora->where('rekomendasi', '!=', TindakanAset::Pertahankan)->whereIn('aset_id', [$aset['A1']->id, $aset['A2']->id, $aset['A3']->id])->count());
});

test('perbandingan butuh dua periode berbeda yang sudah dihitung', function () {
    ['periode' => $periode] = periodeGolden();
    $user = userWithRole(UserRole::Admin);

    $this->actingAs($user)->get(route('hasil.perbandingan'))->assertOk()->assertSee('Belum bisa dibandingkan');
    $this->get(route('hasil.perbandingan', ['lama' => $periode->id, 'baru' => $periode->id]))->assertSessionHasErrors('lama');
});
