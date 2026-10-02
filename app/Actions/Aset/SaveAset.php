<?php

declare(strict_types=1);

namespace App\Actions\Aset;

use App\Models\Aset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class SaveAset
{
    /**
     * Simpan (tambah/ubah) data BMD aset beserta foto baru (kamera/unggah).
     *
     * @param  array<string, mixed>  $data  hasil validated() StoreAsetRequest/UpdateAsetRequest
     * @param  array<int, UploadedFile>  $fotos
     */
    public function execute(array $data, array $fotos = [], ?Aset $aset = null): Aset
    {
        return DB::transaction(function () use ($data, $fotos, $aset): Aset {
            $aset ??= new Aset;
            $aset->fill(Arr::except($data, ['fotos']))->save();

            foreach ($fotos as $foto) {
                $aset->fotos()->create(['path' => $foto->store("aset/{$aset->id}", 'public')]);
            }

            return $aset;
        });
    }
}
