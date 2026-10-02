<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pengaturan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengaturan>
 */
class PengaturanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => 'pengaturan_'.fake()->unique()->word(),
            'value' => fake()->sentence(),
        ];
    }
}
