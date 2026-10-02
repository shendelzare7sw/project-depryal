<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Kriteria;
use App\Models\KriteriaSkala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KriteriaSkala>
 */
class KriteriaSkalaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kriteria_id' => Kriteria::factory(),
            'nilai' => fake()->numberBetween(1, 5),
            'label' => fake()->word(),
            'deskripsi' => fake()->sentence(),
        ];
    }
}
