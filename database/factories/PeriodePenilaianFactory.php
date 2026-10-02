<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusPeriode;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodePenilaian>
 */
class PeriodePenilaianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => 'Penilaian Kelayakan BMD Semester '.fake()->randomElement(['I', 'II']).' '.date('Y'),
            'tanggal_mulai' => fake()->date(),
            'tanggal_selesai' => null,
            'status' => StatusPeriode::Draft,
            'snapshot_kriteria' => null,
            'snapshot_ambang' => null,
            'dihitung_pada' => null,
            'dihitung_oleh' => null,
            'difinalisasi_pada' => null,
            'difinalisasi_oleh' => null,
            'alasan_buka_kembali' => null,
            'created_by' => User::factory(),
        ];
    }
}
