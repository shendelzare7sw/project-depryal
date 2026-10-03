<?php

declare(strict_types=1);

namespace App\Enums;

enum FormatLaporan: string
{
    case Pdf = 'pdf';
    case Xlsx = 'xlsx';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => 'PDF (siap cetak)',
            self::Xlsx => 'Excel (.xlsx)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pdf => 'error',
            self::Xlsx => 'success',
        };
    }
}
