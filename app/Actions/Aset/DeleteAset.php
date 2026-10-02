<?php

declare(strict_types=1);

namespace App\Actions\Aset;

use App\Models\Aset;
use DomainException;

final class DeleteAset
{
    /**
     * Soft delete aset; ditolak bila aset sudah tercatat di periode penilaian final.
     */
    public function execute(Aset $aset): void
    {
        if ($aset->isDipakaiPeriodeFinal()) {
            throw new DomainException('Aset tidak dapat dihapus karena sudah tercatat pada periode penilaian yang final.');
        }

        $aset->delete();
    }
}
