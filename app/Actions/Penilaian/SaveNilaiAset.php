<?php

declare(strict_types=1);

namespace App\Actions\Penilaian;

use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use App\Services\Aset\FotoAset;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class SaveNilaiAset
{
    public function __construct(private readonly FotoAset $fotoAset) {}

    /**
     * Simpan nilai kriteria, deskripsi kondisi, dan foto satu aset dalam periode.
     * Nilai kosong (null) menghapus nilai lama. Bila periode sudah dihitung, hasil MOORA & keputusan
     * dihapus (perlu hitung ulang). Status periode: draft ↔ dinilai sesuai kelengkapan.
     *
     * @param  array<int|string, mixed>  $nilai  [kriteria_id => nilai|null]
     * @param  array<int, UploadedFile>  $fotos
     */
    public function execute(PeriodePenilaian $periode, Aset $aset, array $nilai, ?string $kondisi, array $fotos = []): void
    {
        if ($periode->isFinal()) {
            throw new DomainException('Periode sudah final; nilai tidak dapat diubah. Buka kembali periode bila perlu koreksi.');
        }

        if (! $periode->aset()->whereKey($aset->id)->exists()) {
            throw new DomainException('Aset ini tidak termasuk dalam periode penilaian.');
        }

        DB::transaction(function () use ($periode, $aset, $nilai, $kondisi, $fotos): void {
            foreach (Kriteria::where('is_active', true)->pluck('id') as $kriteriaId) {
                $value = $nilai[$kriteriaId] ?? null;
                $kunci = ['periode_id' => $periode->id, 'aset_id' => $aset->id, 'kriteria_id' => $kriteriaId];

                $value === null || $value === ''
                    ? NilaiKriteriaAset::where($kunci)->delete()
                    : NilaiKriteriaAset::updateOrCreate($kunci, ['nilai' => $value]);
            }

            $periode->aset()->updateExistingPivot($aset->id, ['deskripsi_kondisi' => $kondisi]);

            foreach ($fotos as $foto) {
                $aset->fotos()->create([
                    'periode_id' => $periode->id,
                    'path' => $this->fotoAset->simpan($foto, "aset/{$aset->id}"),
                ]);
            }

            if ($periode->status === StatusPeriode::Dihitung) {
                HasilMoora::where('periode_id', $periode->id)->delete();
                Keputusan::where('periode_id', $periode->id)->delete();
            }

            $periode->update(['status' => $periode->isLengkap() ? StatusPeriode::Dinilai : StatusPeriode::Draft]);
        });
    }
}
