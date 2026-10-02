<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TindakanAset;
use App\Models\Aset;
use App\Models\Keputusan;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Keputusan>
 */
class KeputusanFactory extends Factory
{
    public function definition(): array
    {
        $rekomendasi = fake()->randomElement([TindakanAset::Pertahankan, TindakanAset::Perbaiki, TindakanAset::Hapus]);

        return [
            'periode_id' => PeriodePenilaian::factory(),
            'aset_id' => Aset::factory(),
            'user_id' => User::factory()->pimpinan(),
            'tindakan' => $rekomendasi,
            'rekomendasi_sistem' => $rekomendasi,
            'catatan' => 'Sesuai hasil perhitungan sistem SPK MOORA.',
        ];
    }
}
