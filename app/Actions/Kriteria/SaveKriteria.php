<?php

declare(strict_types=1);

namespace App\Actions\Kriteria;

use App\Models\Kriteria;
use App\Models\KriteriaSkala;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class SaveKriteria
{
    /**
     * Simpan kriteria + rubrik skala. Bobot diinput dalam persen (0–100), disimpan desimal (0–1).
     *
     * @param  array<string, mixed>  $data  hasil validated() StoreKriteriaRequest/UpdateKriteriaRequest
     */
    public function execute(array $data, ?Kriteria $kriteria = null): Kriteria
    {
        return DB::transaction(function () use ($data, $kriteria): Kriteria {
            $kriteria ??= new Kriteria;
            $kriteria->fill(Arr::except($data, ['bobot_persen', 'skala']));
            $kriteria->bobot = round((float) $data['bobot_persen'] / 100, 4);
            $kriteria->save();

            $rentang = range($kriteria->skala_min, $kriteria->skala_maks);
            KriteriaSkala::where('kriteria_id', $kriteria->id)->whereNotIn('nilai', $rentang)->delete();

            foreach ($rentang as $nilai) {
                KriteriaSkala::updateOrCreate(
                    ['kriteria_id' => $kriteria->id, 'nilai' => $nilai],
                    [
                        'label' => $data['skala'][$nilai]['label'] ?? (string) $nilai,
                        'deskripsi' => $data['skala'][$nilai]['deskripsi'] ?? null,
                    ],
                );
            }

            return $kriteria;
        });
    }
}
