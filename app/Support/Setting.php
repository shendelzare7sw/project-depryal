<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Pengaturan;
use Illuminate\Support\Facades\Cache;

class Setting
{
    private const CACHE_KEY = 'sikaset_settings';

    /**
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        $tersimpan = Cache::get(self::CACHE_KEY);

        if (is_array($tersimpan)) {
            return $tersimpan;
        }

        // Tabel belum ada (mis. saat migrasi pertama): kembalikan kosong tanpa menyimpan ke cache.
        try {
            $semua = Pengaturan::pluck('value', 'key')->toArray();
        } catch (\Throwable) {
            return [];
        }

        Cache::forever(self::CACHE_KEY, $semua);

        return $semua;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        Pengaturan::updateOrCreate(['key' => $key], ['value' => $value]);
        self::clearCache();
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function ambangPertahankan(): float
    {
        return (float) self::get('ambang_pertahankan', 66.67);
    }

    public static function ambangPerbaiki(): float
    {
        return (float) self::get('ambang_perbaiki', 33.33);
    }

    /**
     * Menit tanpa aktivitas sebelum pengguna dikeluarkan otomatis (0 = nonaktif).
     */
    public static function batasIdleMenit(): int
    {
        return max(0, (int) self::get('batas_idle_menit', 30));
    }

    public static function namaInstansi(): string
    {
        return (string) self::get('nama_instansi', 'Kecamatan Batuceper');
    }

    public static function alamatInstansi(): string
    {
        return (string) self::get('alamat_instansi', 'Jl. Raya Batuceper No. 1, Kota Tangerang, Banten');
    }

    public static function namaPenandatangan(): string
    {
        return (string) self::get('nama_penandatangan', 'Camat Batuceper');
    }

    public static function nipPenandatangan(): string
    {
        return (string) self::get('nip_penandatangan', '-');
    }

    public static function jabatanPenandatangan(): string
    {
        return (string) self::get('jabatan_penandatangan', 'Camat');
    }
}
