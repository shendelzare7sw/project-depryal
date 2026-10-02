<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TipeKriteria;
use App\Models\Kriteria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kriteria>
 */
class KriteriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => 'C'.fake()->unique()->numberBetween(1, 99),
            'nama' => fake()->words(2, true),
            'tipe' => fake()->randomElement([TipeKriteria::Benefit, TipeKriteria::Cost]),
            'bobot' => 0.25,
            'skala_min' => 1,
            'skala_maks' => 5,
            'urutan' => fake()->numberBetween(1, 10),
            'is_active' => true,
            'keterangan' => fake()->sentence(),
        ];
    }
}
