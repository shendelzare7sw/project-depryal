<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use DomainException;
use Illuminate\Support\Facades\DB;

final class KelolaAsetPeriode
{
    /**
     * Tambahkan aset aktif ke periode yang belum final. Aset yang sudah ada di periode diabaikan.
     * Bila periode sudah dihitung, hasil MOORA & keputusan dihapus (perlu hitung ulang).
     *
     * @param  list<int>  $asetIds
     * @return int jumlah aset yang benar-benar ditambahkan
     */
    public function tambah(PeriodePenilaian $periode, array $asetIds): int
    {
        $this->pastikanBelumFinal($periode);

        $baru = Aset::whereIn('id', $asetIds)->where('status', StatusAset::Aktif->value)
            ->whereNotIn('id', $periode->aset()->pluck('aset.id'))->pluck('id')->all();

        if ($baru === []) {
            return 0;
        }

        DB::transaction(function () use ($periode, $baru): void {
            $periode->aset()->attach($baru);
            $this->sesuaikanStatus($periode);
        });

        return count($baru);
    }

    /**
     * Keluarkan satu aset dari periode beserta nilainya. Periode harus tetap berisi minimal 2 aset.
     */
    public function keluarkan(PeriodePenilaian $periode, Aset $aset): void
    {
        $this->pastikanBelumFinal($periode);

        if (! $periode->aset()->whereKey($aset->id)->exists()) {
            throw new DomainException('Aset ini tidak termasuk dalam periode penilaian.');
        }

        if ($periode->aset()->count() <= 2) {
            throw new DomainException('Periode harus berisi minimal 2 aset. Tambahkan aset lain sebelum mengeluarkan aset ini.');
        }

        DB::transaction(function () use ($periode, $aset): void {
            NilaiKriteriaAset::where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->delete();
            $periode->aset()->detach($aset->id);
            $this->sesuaikanStatus($periode);
        });
    }

    private function pastikanBelumFinal(PeriodePenilaian $periode): void
    {
        if ($periode->isFinal()) {
            throw new DomainException('Periode sudah final; daftar aset tidak dapat diubah. Buka kembali periode bila perlu koreksi.');
        }
    }

    private function sesuaikanStatus(PeriodePenilaian $periode): void
    {
        if ($periode->status === StatusPeriode::Dihitung) {
            HasilMoora::where('periode_id', $periode->id)->delete();
            Keputusan::where('periode_id', $periode->id)->delete();
        }

        $periode->update(['status' => $periode->isLengkap() ? StatusPeriode::Dinilai : StatusPeriode::Draft]);
    }
}
