<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\StatusPeriode;
use App\Models\PeriodePenilaian;
use DomainException;

final class DeletePeriode
{
    /**
     * Hapus periode yang belum dihitung (draft/dinilai) beserta nilainya. Foto aset tetap tersimpan.
     */
    public function execute(PeriodePenilaian $periode): void
    {
        if (! in_array($periode->status, [StatusPeriode::Draft, StatusPeriode::Dinilai], true)) {
            throw new DomainException('Hanya periode berstatus Draft atau Dinilai yang dapat dihapus.');
        }

        $periode->delete();
    }
}
