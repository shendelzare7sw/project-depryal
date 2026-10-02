<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Notifications\SistemNotification;
use App\Services\Notifikasi;
use DomainException;

final class ReopenPeriode
{
    public function __construct(private readonly Notifikasi $notifikasi) {}

    /**
     * Buka kembali periode final → status dihitung (keputusan tetap ada, dapat diubah pimpinan).
     * Alasan wajib dan tercatat pada periode.
     */
    public function execute(PeriodePenilaian $periode, User $by, string $alasan): void
    {
        if (! $periode->isFinal()) {
            throw new DomainException('Hanya periode berstatus final yang dapat dibuka kembali.');
        }

        if (PeriodePenilaian::aktif()->whereKeyNot($periode->id)->exists()) {
            throw new DomainException('Masih ada periode lain yang belum final. Hanya satu periode aktif yang diizinkan.');
        }

        $periode->update([
            'status' => StatusPeriode::Dihitung,
            'alasan_buka_kembali' => $alasan,
            'difinalisasi_pada' => null,
            'difinalisasi_oleh' => null,
        ]);

        $this->notifikasi->kirimKeRole([UserRole::Pimpinan, UserRole::Admin], new SistemNotification(
            judul: 'Periode dibuka kembali',
            pesan: "\"{$periode->nama}\" dibuka kembali oleh {$by->name}: {$alasan}",
            url: route('periode.show', $periode),
            icon: 'lock-open',
            tone: 'warning',
        ), $by);
    }
}
