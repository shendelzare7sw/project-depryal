<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\UserRole;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Notifications\SistemNotification;
use App\Services\Notifikasi;

/**
 * Use case "Hitung MOORA" dari UI: jalankan CalculatePeriode (Fase 1, tidak diubah)
 * lalu beri tahu Pimpinan bahwa peringkat siap diputuskan.
 */
final class HitungPeriode
{
    public function __construct(
        private readonly CalculatePeriode $calculate,
        private readonly Notifikasi $notifikasi,
    ) {}

    public function execute(PeriodePenilaian $periode, User $by): void
    {
        $this->calculate->execute($periode, $by);

        $jumlah = $periode->hasilMoora()->count();
        $this->notifikasi->kirimKeRole([UserRole::Pimpinan, UserRole::Admin], new SistemNotification(
            judul: 'Peringkat MOORA siap ditinjau',
            pesan: "{$jumlah} aset pada periode \"{$periode->nama}\" menunggu keputusan pimpinan.",
            url: route('peringkat.index', $periode),
            icon: 'chart-bar',
            tone: 'primary',
        ), $by);
    }
}
