<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Aset;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NilaiKriteriaAset>
 */
class NilaiKriteriaAsetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'periode_id' => PeriodePenilaian::factory(),
            'aset_id' => Aset::factory(),
            'kriteria_id' => Kriteria::factory(),
            'nilai' => fake()->randomFloat(2, 1, 5),
        ];
    }
}
