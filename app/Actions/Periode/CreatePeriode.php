<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreatePeriode
{
    /**
     * Buat periode baru beserta daftar asetnya. Hanya boleh ada satu periode non-final.
     *
     * @param  array<string, mixed>  $data  nama, tanggal_mulai, tanggal_selesai?, cakupan (semua|kategori|perhatian|pilih),
     *                                      kategori_ids?, aset_ids?, created_by
     */
    public function execute(array $data): PeriodePenilaian
    {
        if (PeriodePenilaian::aktif()->exists()) {
            throw new DomainException('Masih ada periode yang belum final. Selesaikan periode tersebut sebelum membuat periode baru.');
        }

        $asetIds = $this->asetIds($data);

        if (count($asetIds) < 2) {
            throw new DomainException('Periode membutuhkan minimal 2 aset aktif. Ubah cakupan aset yang dipilih.');
        }

        return DB::transaction(function () use ($data, $asetIds): PeriodePenilaian {
            $periode = PeriodePenilaian::create(Arr::only($data, ['nama', 'tanggal_mulai', 'tanggal_selesai', 'created_by'])
                + ['status' => StatusPeriode::Draft]);
            $periode->aset()->attach($asetIds);

            return $periode;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function asetIds(array $data): array
    {
        $query = Aset::query()->where('status', StatusAset::Aktif->value);

        match ($data['cakupan']) {
            'kategori' => $query->whereIn('kategori_aset_id', $data['kategori_ids'] ?? []),
            'perhatian' => $query->perluPerhatian(),
            'pilih' => $query->whereIn('id', $data['aset_ids'] ?? []),
            default => null,
        };

        return $query->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }
}
