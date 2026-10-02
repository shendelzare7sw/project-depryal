<?php

declare(strict_types=1);

use App\Actions\Periode\CalculatePeriode;
use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Enums\TipeKriteria;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;
use App\Models\Pengaturan;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\Moora\MooraCalculator;

// ── Helper buat periode + aset + nilai golden ─────────────────────────────────
function setupGoldenPeriode(): array
{
    $admin = User::factory()->operator()->create();

    // Pengaturan ambang default
    Pengaturan::firstOrCreate(['key' => 'ambang_pertahankan'], ['value' => '66.67']);
    Pengaturan::firstOrCreate(['key' => 'ambang_perbaiki'], ['value' => '33.33']);

    // 3 kriteria aktif (golden dataset)
    $kF = Kriteria::create(['kode' => 'C1', 'nama' => 'Fungsi',      'tipe' => TipeKriteria::Benefit, 'bobot' => 0.40, 'urutan' => 1, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);
    $kE = Kriteria::create(['kode' => 'C2', 'nama' => 'Efektivitas', 'tipe' => TipeKriteria::Benefit, 'bobot' => 0.35, 'urutan' => 2, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);
    $kB = Kriteria::create(['kode' => 'C3', 'nama' => 'Biaya',       'tipe' => TipeKriteria::Cost,    'bobot' => 0.25, 'urutan' => 3, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);

    // 4 aset (A1–A4) lewat factory
    $asets = Aset::factory(4)->create();

    // Periode
    $periode = PeriodePenilaian::create([
        'nama' => 'Periode Test',
        'tanggal_mulai' => now()->toDateString(),
        'status' => StatusPeriode::Draft,
        'created_by' => $admin->id,
    ]);

    // Attach aset ke periode
    $periode->aset()->attach($asets->pluck('id'));

    // Matriks golden: A1=[4,3,2], A2=[2,2,4], A3=[5,4,1], A4=[3,5,3]
    $matrix = [
        $asets[0]->id => [$kF->id => 4, $kE->id => 3, $kB->id => 2],
        $asets[1]->id => [$kF->id => 2, $kE->id => 2, $kB->id => 4],
        $asets[2]->id => [$kF->id => 5, $kE->id => 4, $kB->id => 1],
        $asets[3]->id => [$kF->id => 3, $kE->id => 5, $kB->id => 3],
    ];

    foreach ($matrix as $asetId => $vals) {
        foreach ($vals as $kritId => $val) {
            NilaiKriteriaAset::create([
                'periode_id' => $periode->id,
                'aset_id' => $asetId,
                'kriteria_id' => $kritId,
                'nilai' => $val,
            ]);
        }
    }

    return compact('admin', 'periode', 'asets', 'kF', 'kE', 'kB');
}

it('menghitung MOORA dan menyimpan hasil ke DB', function (): void {
    ['admin' => $admin, 'periode' => $periode, 'asets' => $asets] = setupGoldenPeriode();

    $action = new CalculatePeriode(new MooraCalculator);
    $action->execute($periode, $admin);

    $periode->refresh();
    expect($periode->status)->toBe(StatusPeriode::Dihitung);
    expect(HasilMoora::where('periode_id', $periode->id)->count())->toBe(4);

    // Ranking A3 harus 1
    $hasilA3 = HasilMoora::where('periode_id', $periode->id)
        ->where('aset_id', $asets[2]->id)
        ->first();

    expect($hasilA3)->not->toBeNull();
    expect($hasilA3->ranking)->toBe(1);
    expect(abs($hasilA3->yi - 0.4170))->toBeLessThan(1e-3);
    expect(abs($hasilA3->skor_relatif - 100.0))->toBeLessThan(0.1);
});

it('menyimpan snapshot_kriteria dan snapshot_ambang', function (): void {
    ['admin' => $admin, 'periode' => $periode] = setupGoldenPeriode();

    (new CalculatePeriode(new MooraCalculator))->execute($periode, $admin);

    $periode->refresh();
    expect($periode->snapshot_kriteria)->toBeArray()->not->toBeEmpty();
    expect($periode->snapshot_ambang)->toHaveKeys(['pertahankan', 'perbaiki']);
    expect($periode->dihitung_oleh)->toBe($admin->id);
});

it('menolak periode yang sudah final', function (): void {
    $admin = User::factory()->operator()->create();
    $periode = PeriodePenilaian::create([
        'nama' => 'Final Test',
        'tanggal_mulai' => now()->toDateString(),
        'status' => StatusPeriode::Final,
        'created_by' => $admin->id,
    ]);

    expect(fn () => (new CalculatePeriode(new MooraCalculator))->execute($periode, $admin))
        ->toThrow(DomainException::class, 'sudah final');
});

it('menolak jika bobot kriteria bukan 100%', function (): void {
    $admin = User::factory()->operator()->create();

    Pengaturan::firstOrCreate(['key' => 'ambang_pertahankan'], ['value' => '66.67']);
    Pengaturan::firstOrCreate(['key' => 'ambang_perbaiki'], ['value' => '33.33']);

    // Bobot total 0,90 (bukan 1,00)
    Kriteria::create(['kode' => 'C1', 'nama' => 'Test 1', 'tipe' => TipeKriteria::Benefit, 'bobot' => 0.50, 'urutan' => 1, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);
    Kriteria::create(['kode' => 'C2', 'nama' => 'Test 2', 'tipe' => TipeKriteria::Cost,    'bobot' => 0.40, 'urutan' => 2, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);

    $asets = Aset::factory(2)->create();
    $periode = PeriodePenilaian::create([
        'nama' => 'Test Bobot',
        'tanggal_mulai' => now()->toDateString(),
        'status' => StatusPeriode::Draft,
        'created_by' => $admin->id,
    ]);
    $periode->aset()->attach($asets->pluck('id'));

    expect(fn () => (new CalculatePeriode(new MooraCalculator))->execute($periode, $admin))
        ->toThrow(DomainException::class, 'bobot');
});

it('menolak jika nilai belum lengkap', function (): void {
    ['admin' => $admin, 'periode' => $periode] = setupGoldenPeriode();

    // Hapus satu nilai → tidak lengkap
    NilaiKriteriaAset::where('periode_id', $periode->id)->first()->delete();

    expect(fn () => (new CalculatePeriode(new MooraCalculator))->execute($periode, $admin))
        ->toThrow(DomainException::class, 'nilai');
});

it('menolak jika kurang dari 2 aset', function (): void {
    $admin = User::factory()->operator()->create();

    Pengaturan::firstOrCreate(['key' => 'ambang_pertahankan'], ['value' => '66.67']);
    Pengaturan::firstOrCreate(['key' => 'ambang_perbaiki'], ['value' => '33.33']);

    // Buat 2 kriteria aktif dengan total bobot = 1,00 agar guard bobot & kriteria lolos
    Kriteria::create(['kode' => 'C1', 'nama' => 'Test 1', 'tipe' => TipeKriteria::Benefit, 'bobot' => 0.60, 'urutan' => 1, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);
    Kriteria::create(['kode' => 'C2', 'nama' => 'Test 2', 'tipe' => TipeKriteria::Cost,    'bobot' => 0.40, 'urutan' => 2, 'is_active' => true, 'skala_min' => 1, 'skala_maks' => 5]);

    $aset = Aset::factory()->create();
    $periode = PeriodePenilaian::create([
        'nama' => 'Satu Aset',
        'tanggal_mulai' => now()->toDateString(),
        'status' => StatusPeriode::Draft,
        'created_by' => $admin->id,
    ]);
    $periode->aset()->attach([$aset->id]);

    expect(fn () => (new CalculatePeriode(new MooraCalculator))->execute($periode, $admin))
        ->toThrow(DomainException::class, 'Minimal 2 aset');
});

it('hitung ulang menghapus hasil_moora dan keputusan lama', function (): void {
    ['admin' => $admin, 'periode' => $periode, 'asets' => $asets] = setupGoldenPeriode();

    $action = new CalculatePeriode(new MooraCalculator);
    $action->execute($periode, $admin);

    // Tambahkan keputusan dummy
    Keputusan::create([
        'periode_id' => $periode->id,
        'aset_id' => $asets[0]->id,
        'user_id' => $admin->id,
        'tindakan' => TindakanAset::Pertahankan,
        'rekomendasi_sistem' => TindakanAset::Pertahankan,
    ]);

    expect(Keputusan::where('periode_id', $periode->id)->count())->toBe(1);

    $periode->refresh();

    // Hitung ulang
    $action->execute($periode, $admin);

    // Keputusan lama harus terhapus
    expect(Keputusan::where('periode_id', $periode->id)->count())->toBe(0);
    expect(HasilMoora::where('periode_id', $periode->id)->count())->toBe(4);
});
