<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case Pimpinan = 'pimpinan';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Operator => 'Operator (Pengurus Barang)',
            self::Pimpinan => 'Pimpinan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'primary',
            self::Operator => 'secondary',
            self::Pimpinan => 'accent',
        };
    }
}
