<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\JenisLaporan;
use App\Models\Laporan;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laporan>
 */
class LaporanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'periode_id' => PeriodePenilaian::factory(),
            'jenis' => fake()->randomElement([JenisLaporan::Peringkat, JenisLaporan::Keputusan, JenisLaporan::Lengkap]),
            'format' => fake()->randomElement(['pdf', 'xlsx']),
            'nama_file' => 'laporan_'.fake()->numerify('####').'.pdf',
            'path' => 'laporan/dummy.pdf',
        ];
    }
}
