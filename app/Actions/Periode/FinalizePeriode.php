<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\Keputusan;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Notifications\SistemNotification;
use App\Services\Notifikasi;
use DomainException;
use Illuminate\Support\Facades\DB;

final class FinalizePeriode
{
    public function __construct(private readonly Notifikasi $notifikasi) {}

    /**
     * Kunci periode: semua aset wajib sudah diputuskan; status aset diperbarui sesuai keputusan.
     */
    public function execute(PeriodePenilaian $periode, User $by): void
    {
        if ($periode->status !== StatusPeriode::Dihitung) {
            throw new DomainException('Hanya periode berstatus Dihitung yang dapat difinalisasi.');
        }

        $total = $periode->hasilMoora()->count();
        $keputusan = Keputusan::where('periode_id', $periode->id)->get();

        if ($total === 0 || $keputusan->count() < $total) {
            throw new DomainException('Masih ada '.($total - $keputusan->count()).' aset yang belum diputuskan.');
        }

        DB::transaction(function () use ($periode, $by, $keputusan): void {
            // Update per model (bukan mass update) agar perubahan status tercatat di audit log.
            foreach (Aset::withTrashed()->whereIn('id', $keputusan->pluck('aset_id'))->get()->keyBy('id') as $id => $aset) {
                $aset->update(['status' => $keputusan->firstWhere('aset_id', $id)?->tindakan->statusAset()]);
            }

            $periode->update([
                'status' => StatusPeriode::Final,
                'difinalisasi_pada' => now(),
                'difinalisasi_oleh' => $by->id,
            ]);
        });

        $this->notifikasi->kirimKeRole([UserRole::Operator, UserRole::Admin], new SistemNotification(
            judul: 'Periode difinalisasi',
            pesan: "\"{$periode->nama}\" difinalisasi oleh {$by->name}. Status {$total} aset diperbarui sesuai keputusan.",
            url: route('peringkat.index', $periode),
            icon: 'lock-closed',
            tone: 'success',
        ), $by);
    }
}
