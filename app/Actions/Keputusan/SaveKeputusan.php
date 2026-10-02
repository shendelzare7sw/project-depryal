<?php

declare(strict_types=1);

namespace App\Actions\Keputusan;

use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\PeriodePenilaian;
use App\Models\User;
use DomainException;

final class SaveKeputusan
{
    /**
     * Simpan keputusan pimpinan untuk satu aset. Catatan wajib (min 10 karakter) bila berbeda dari rekomendasi.
     */
    public function execute(PeriodePenilaian $periode, Aset $aset, User $by, TindakanAset $tindakan, ?string $catatan): Keputusan
    {
        if ($periode->status !== StatusPeriode::Dihitung) {
            throw new DomainException($periode->isFinal()
                ? 'Periode sudah final; keputusan terkunci.'
                : 'Periode belum dihitung. Keputusan baru dapat diberikan setelah Hitung MOORA.');
        }

        $hasil = HasilMoora::where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->first()
            ?? throw new DomainException('Aset ini tidak memiliki hasil MOORA pada periode tersebut.');

        if ($tindakan !== $hasil->rekomendasi && mb_strlen(trim((string) $catatan)) < 10) {
            throw new DomainException('Catatan minimal 10 karakter wajib diisi karena keputusan berbeda dari rekomendasi sistem.');
        }

        return Keputusan::updateOrCreate(
            ['periode_id' => $periode->id, 'aset_id' => $aset->id],
            ['user_id' => $by->id, 'tindakan' => $tindakan, 'rekomendasi_sistem' => $hasil->rekomendasi, 'catatan' => $catatan],
        );
    }
}
