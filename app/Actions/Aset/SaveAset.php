<?php

declare(strict_types=1);

namespace App\Actions\Aset;

use App\Actions\Periode\KelolaAsetPeriode;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use App\Services\Aset\FotoAset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class SaveAset
{
    public function __construct(
        private readonly KelolaAsetPeriode $kelolaPeriode,
        private readonly FotoAset $fotoAset,
    ) {}

    /**
     * Simpan (tambah/ubah) data BMD aset beserta foto baru (kamera/unggah).
     * masuk_periode=true → aset (aktif) langsung ditambahkan ke periode yang sedang berjalan.
     *
     * @param  array<string, mixed>  $data  hasil validated() StoreAsetRequest/UpdateAsetRequest
     * @param  array<int, UploadedFile>  $fotos
     */
    public function execute(array $data, array $fotos = [], ?Aset $aset = null): Aset
    {
        return DB::transaction(function () use ($data, $fotos, $aset): Aset {
            $aset ??= new Aset;
            $aset->fill(Arr::except($data, ['fotos', 'masuk_periode']))->save();

            foreach ($fotos as $foto) {
                $aset->fotos()->create(['path' => $this->fotoAset->simpan($foto, "aset/{$aset->id}")]);
            }

            $periode = ($data['masuk_periode'] ?? false) ? PeriodePenilaian::aktif()->first() : null;
            if ($periode) {
                $this->kelolaPeriode->tambah($periode, [$aset->id]);
            }

            return $aset;
        });
    }
}
