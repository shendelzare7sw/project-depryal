<?php

declare(strict_types=1);

namespace App\Actions\Kriteria;

use App\Models\Kriteria;
use DomainException;

final class DeleteKriteria
{
    /**
     * Kriteria yang sudah punya nilai penilaian (termasuk periode final) tidak boleh dihapus,
     * karena FK nilai_kriteria_aset bersifat cascade — cukup dinonaktifkan.
     */
    public function execute(Kriteria $kriteria): void
    {
        if ($kriteria->nilaiAset()->exists()) {
            throw new DomainException('Kriteria sudah dipakai dalam penilaian sehingga tidak dapat dihapus. Nonaktifkan saja.');
        }

        $kriteria->delete();
    }
}
