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
}
