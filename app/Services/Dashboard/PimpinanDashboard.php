<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Models\Laporan;
use App\Models\PeriodePenilaian;
use App\Services\Peringkat\PeringkatData;

/**
 * Dashboard Pimpinan: aset menunggu keputusan, distribusi rekomendasi, 5 aset prioritas.
 */
final class PimpinanDashboard
{
    public function __construct(private readonly PeringkatData $peringkat) {}

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $periode = PeriodePenilaian::where('status', StatusPeriode::Dihitung->value)->latest('id')->first()
            ?? PeriodePenilaian::where('status', StatusPeriode::Final->value)->latest('id')->first();
        $data = $periode ? $this->peringkat->index($periode) : null;
        $baris = $data['baris'] ?? collect();
        $belum = $baris->filter(fn (array $b) => $b['keputusan'] === null);

        return [
            'periode' => $periode,
            'total' => $data['total'] ?? 0,
            'diputuskan' => $data['diputuskan'] ?? 0,
            'menunggu' => $belum->count(),
            'belumPertama' => $data['belumPertama'] ?? null,
            'bisaFinalisasi' => $data['bisaFinalisasi'] ?? false,
            'distribusi' => collect(TindakanAset::cases())->map(fn (TindakanAset $t) => [
                'tindakan' => $t,
                'jumlah' => $data['ringkasan'][$t->value] ?? 0,
                'persen' => ($data['total'] ?? 0) > 0 ? round(($data['ringkasan'][$t->value] ?? 0) / $data['total'] * 100, 1) : 0,
            ])->all(),
            'prioritas' => ($belum->isNotEmpty() ? $belum : $baris)->sortBy(fn (array $b) => $b['hasil']->skor_relatif)->take(5)->values(),
            'riwayat' => PeriodePenilaian::where('status', StatusPeriode::Final->value)->withCount('aset')->latest('difinalisasi_pada')->limit(4)->get(),
            'laporan' => Laporan::with('periode')->latest()->limit(4)->get(),
        ];
    }
}
