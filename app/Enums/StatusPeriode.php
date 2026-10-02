<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusPeriode: string
{
    case Draft = 'draft';
    case Dinilai = 'dinilai';
    case Dihitung = 'dihitung';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Dinilai => 'Dinilai',
            self::Dihitung => 'Dihitung',
            self::Final => 'Final',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'ghost',
            self::Dinilai => 'info',
            self::Dihitung => 'warning',
            self::Final => 'success',
        };
    }
}
