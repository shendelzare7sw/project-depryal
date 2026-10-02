<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\KategoriAset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KategoriAset>
 */
class KategoriAsetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => 'KAT-'.fake()->unique()->numerify('###'),
            'nama' => fake()->randomElement(['Gedung Kantor', 'Gedung Posyandu', 'Gedung Serbaguna', 'Bangunan Air/Drainase', 'Gedung Sekolah/Pendidikan']),
        ];
    }
}
