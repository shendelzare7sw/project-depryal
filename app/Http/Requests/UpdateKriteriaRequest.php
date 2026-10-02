<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Kriteria;

class UpdateKriteriaRequest extends StoreKriteriaRequest
{
    /**
     * Kriteria yang sudah dipakai periode final: tipe & skala dipaksa tetap (input diabaikan).
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $kriteria = $this->route('kriteria');

        if ($kriteria instanceof Kriteria && $kriteria->isTerkunci()) {
            $this->merge([
                'tipe' => $kriteria->tipe->value,
                'skala_min' => $kriteria->skala_min,
                'skala_maks' => $kriteria->skala_maks,
            ]);
        }
    }
}
