<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Pengganti pagination bernomor: daftar selalu dimulai dari data pertama dan bertambah per "langkah"
 * lewat tombol "Tampilkan lebih banyak" (parameter ?tampil=N). Lebih mudah di ponsel, filter tetap terbawa.
 */
final class Tampil
{
    public const LANGKAH = 15;

    /** Batas atas agar halaman tetap ringan; lebih dari ini pengguna diminta memakai filter/pencarian. */
    public const MAKS = 300;

    public static function jumlah(int $langkah = self::LANGKAH): int
    {
        $diminta = (int) request()->query('tampil', (string) $langkah);

        return min(self::MAKS, max($langkah, (int) ceil($diminta / $langkah) * $langkah));
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>|Relation<TModel, *, *>  $query
     * @return LengthAwarePaginator<int, TModel>
     */
    public static function ambil(Builder|Relation $query, int $langkah = self::LANGKAH): LengthAwarePaginator
    {
        return $query->paginate(self::jumlah($langkah), ['*'], 'halaman', 1)->withQueryString();
    }
}
