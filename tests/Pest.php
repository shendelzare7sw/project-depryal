<?php

use App\Enums\StatusPeriode;
use App\Enums\TipeKriteria;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function userWithRole(UserRole $role): User
{
    return User::factory()->create(['role' => $role, 'is_active' => true]);
}

/**
 * Buat periode final yang memuat aset (dan opsional nilai untuk kriteria) — untuk uji penguncian.
 */
function periodeFinalDengan(Aset $aset, ?Kriteria $kriteria = null): PeriodePenilaian
{
    $periode = PeriodePenilaian::factory()->create(['status' => StatusPeriode::Final]);
    $periode->aset()->attach($aset->id);

    if ($kriteria) {
        NilaiKriteriaAset::create([
            'periode_id' => $periode->id, 'aset_id' => $aset->id, 'kriteria_id' => $kriteria->id, 'nilai' => 3,
        ]);
    }

    return $periode;
}

/**
 * 3 kriteria aktif (bobot total 100%) beserta rubrik 1–5.
 *
 * @return list<Kriteria>
 */
function siapkanKriteria(): array
{
    return collect([['C1', TipeKriteria::Benefit, 0.40], ['C2', TipeKriteria::Benefit, 0.35], ['C3', TipeKriteria::Cost, 0.25]])
        ->map(function (array $k, int $i) {
            $kriteria = Kriteria::factory()->create(['kode' => $k[0], 'tipe' => $k[1], 'bobot' => $k[2], 'urutan' => $i + 1]);
            foreach (range(1, 5) as $n) {
                $kriteria->skala()->create(['nilai' => $n, 'label' => "Skala {$n}"]);
            }

            return $kriteria;
        })->all();
}

function periodeDraft(int $jumlahAset = 3): PeriodePenilaian
{
    $periode = PeriodePenilaian::factory()->create(['status' => StatusPeriode::Draft]);
    $periode->aset()->attach(Aset::factory()->count($jumlahAset)->create()->pluck('id'));

    return $periode;
}

/**
 * @param  list<Kriteria>  $kriteria
 */
function isiSemuaNilai(PeriodePenilaian $periode, array $kriteria): void
{
    foreach ($periode->aset as $i => $aset) {
        foreach ($kriteria as $j => $k) {
            NilaiKriteriaAset::create(['periode_id' => $periode->id, 'aset_id' => $aset->id, 'kriteria_id' => $k->id, 'nilai' => ($i + $j) % 5 + 1]);
        }
    }
    $periode->update(['status' => StatusPeriode::Dinilai]);
}
