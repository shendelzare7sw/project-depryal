<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisLaporan: string
{
    case Peringkat = 'peringkat';
    case Keputusan = 'keputusan';
    case Lengkap = 'lengkap';

    public function label(): string
    {
        return match ($this) {
            self::Peringkat => 'Laporan Peringkat Kelayakan (MOORA)',
            self::Keputusan => 'Laporan Keputusan Tindakan Aset',
            self::Lengkap => 'Laporan Lengkap Penilaian & Keputusan',
        };
    }
}
