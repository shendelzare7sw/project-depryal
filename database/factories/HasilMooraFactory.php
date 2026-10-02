<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TindakanAset;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\PeriodePenilaian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HasilMoora>
 */
class HasilMooraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'periode_id' => PeriodePenilaian::factory(),
            'aset_id' => Aset::factory(),
            'yi' => fake()->randomFloat(6, -0.5, 0.5),
            'skor_relatif' => fake()->randomFloat(2, 0, 100),
            'ranking' => fake()->numberBetween(1, 20),
            'rekomendasi' => fake()->randomElement([TindakanAset::Pertahankan, TindakanAset::Perbaiki, TindakanAset::Hapus]),
            'detail' => [
                'normalized' => [],
                'weighted' => [],
                'benefit_sum' => 0.5,
                'cost_sum' => 0.2,
            ],
        ];
    }
}
