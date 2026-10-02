<?php

declare(strict_types=1);

namespace App\Services\Peringkat;

use App\Enums\TipeKriteria;
use App\Models\Kriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Aturan bisnis #8 (wawancara 3.1.1): Fungsi ≥ 4 dan Biaya Pemeliharaan = skala maksimum
 * → "Berfungsi tetapi biaya tinggi — pertimbangkan penghapusan". Hanya informasi.
 *
 * Kriteria dikenali dari namanya: benefit yang mengandung "fungsi" dan cost yang mengandung "biaya".
 */
final class PeringatanBiayaTinggi
{
    public const PESAN = 'Berfungsi tetapi biaya tinggi — pertimbangkan penghapusan';

    private ?Kriteria $fungsi;

    private ?Kriteria $biaya;

    /**
     * @param  Collection<int, Kriteria>  $kriteria
     */
    public function __construct(Collection $kriteria)
    {
        $this->fungsi = $kriteria->first(fn (Kriteria $k) => $k->tipe === TipeKriteria::Benefit && Str::contains(Str::lower($k->nama), 'fungsi'));
        $this->biaya = $kriteria->first(fn (Kriteria $k) => $k->tipe === TipeKriteria::Cost && Str::contains(Str::lower($k->nama), 'biaya'));
    }

    /**
     * @param  array<int|string, float|int|string>  $nilai  [kriteria_id => nilai]
     */
    public function berlaku(array $nilai): bool
    {
        if (! $this->fungsi || ! $this->biaya) {
            return false;
        }

        return (float) ($nilai[$this->fungsi->id] ?? 0) >= 4
            && (float) ($nilai[$this->biaya->id] ?? 0) >= $this->biaya->skala_maks;
    }
}
