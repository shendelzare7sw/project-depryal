<?php

declare(strict_types=1);

namespace App\Enums;

enum TindakanAset: string
{
    case Pertahankan = 'pertahankan';
    case Perbaiki = 'perbaiki';
    case Hapus = 'hapus';

    public function label(): string
    {
        return match ($this) {
            self::Pertahankan => 'Pertahankan',
            self::Perbaiki => 'Perbaiki',
            self::Hapus => 'Hapus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pertahankan => 'success',
            self::Perbaiki => 'warning',
            self::Hapus => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pertahankan => 'shield-check',
            self::Perbaiki => 'wrench-screwdriver',
            self::Hapus => 'archive-box-x-mark',
        };
    }

    public function keterangan(): string
    {
        return match ($this) {
            self::Pertahankan => 'Aset layak dipakai; lanjutkan pemeliharaan rutin.',
            self::Perbaiki => 'Aset perlu diperbaiki/direhabilitasi agar berfungsi optimal.',
            self::Hapus => 'Aset diusulkan untuk penghapusan BMD.',
        };
    }

    /**
     * Status aset setelah periode difinalisasi.
     */
    public function statusAset(): StatusAset
    {
        return match ($this) {
            self::Pertahankan => StatusAset::Aktif,
            self::Perbaiki => StatusAset::DalamPerbaikan,
            self::Hapus => StatusAset::DiusulkanHapus,
        };
    }
}
