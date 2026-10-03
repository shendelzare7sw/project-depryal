<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Kriteria;
use App\Models\PeriodePenilaian;
use App\Services\Periode\PeriodeDetail;

/**
 * Dashboard Operator (Pengurus Barang): progres kerja dari data sampai hasil.
 */
final class OperatorDashboard
{
    public function __construct(private readonly PeriodeDetail $periodeDetail) {}

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $totalAset = Aset::count();
        $totalBobot = Kriteria::totalBobotAktifPersen();
        $kriteriaAktif = Kriteria::where('is_active', true)->count();
        $periode = PeriodePenilaian::aktif()->latest('id')->first();
        $progres = $periode?->progres();

        return [
            'periode' => $periode,
            'progres' => $progres,
            'aksiUtama' => $this->aksiUtama($periode, $progres),
            'stats' => [
                ['label' => 'Total aset BMD', 'value' => $totalAset, 'icon' => 'building-office-2', 'tone' => 'brand', 'meta' => KategoriAset::count().' kategori', 'href' => route('aset.index')],
                ['label' => 'Perlu perhatian', 'value' => Aset::perluPerhatian()->count(), 'icon' => 'exclamation-triangle', 'tone' => 'amber', 'meta' => 'Sisa UEB ≤ 3 tahun'],
                ['label' => 'Kriteria aktif', 'value' => $kriteriaAktif, 'icon' => 'scale', 'tone' => abs($totalBobot - 100) < 0.0001 ? 'emerald' : 'rose', 'meta' => "Total bobot {$totalBobot}%", 'href' => route('kriteria.index')],
                ['label' => 'Nilai periode aktif', 'value' => $progres ? "{$progres['terisi']}/{$progres['diperlukan']}" : '—', 'icon' => 'calendar-days', 'tone' => 'sky', 'meta' => $periode ? $periode->status->label().' · '.$progres['aset_lengkap'].' aset lengkap' : 'Belum ada periode aktif'],
            ],
            'langkah' => $this->langkah($totalAset, $totalBobot, $kriteriaAktif, $periode, $progres),
            'asetPerhatian' => Aset::with('kategori')->perluPerhatian()->orderBy('sisa_ueb')->limit(5)->get(),
            'kriteria' => Kriteria::aktif()->get(),
            'totalBobot' => $totalBobot,
            'kategori' => KategoriAset::withCount('aset')->orderByDesc('aset_count')->limit(6)->get(),
            'totalAset' => $totalAset,
        ];
    }

    /**
     * Tombol utama kontekstual sesuai posisi alur kerja.
     *
     * @param  array<string, int>|null  $progres
     * @return array{label: string, href: string, icon: string}
     */
    private function aksiUtama(?PeriodePenilaian $periode, ?array $progres): array
    {
        if (! $periode) {
            return ['label' => 'Buat Periode Penilaian', 'href' => route('periode.create'), 'icon' => 'plus'];
        }

        if ($periode->status === StatusPeriode::Dihitung) {
            return ['label' => 'Lihat Peringkat', 'href' => route('peringkat.index', $periode), 'icon' => 'chart-bar'];
        }

        $aset = $progres && $progres['terisi'] < $progres['diperlukan'] ? $this->periodeDetail->asetBelumLengkapPertama($periode) : null;

        return $aset
            ? ['label' => 'Lanjutkan Penilaian', 'href' => route('penilaian.edit', [$periode, $aset]), 'icon' => 'clipboard-document-check']
            : ['label' => 'Hitung MOORA', 'href' => route('periode.show', $periode), 'icon' => 'calculator'];
    }

    /**
     * Urutan kerja SPK: data → kriteria → periode → hitung → keputusan/final.
     *
     * @param  array<string, int>|null  $progres
     * @return list<array{label: string, hint: string, done: bool, href: string|null}>
     */
    private function langkah(int $totalAset, float $totalBobot, int $kriteriaAktif, ?PeriodePenilaian $periode, ?array $progres): array
    {
        $status = $periode?->status;
        $adaFinal = PeriodePenilaian::where('status', StatusPeriode::Final->value)->exists();

        return [
            ['label' => 'Lengkapi data aset BMD', 'hint' => "{$totalAset} aset tercatat", 'done' => $totalAset >= 2, 'href' => route('aset.index')],
            ['label' => 'Atur kriteria & bobot 100%', 'hint' => "{$kriteriaAktif} kriteria · bobot {$totalBobot}%", 'done' => $kriteriaAktif >= 2 && abs($totalBobot - 100) < 0.0001, 'href' => route('kriteria.index')],
            ['label' => 'Buat periode & input nilai', 'hint' => $periode ? "{$progres['persen']}% nilai \"{$periode->nama}\"" : 'Belum ada periode aktif', 'done' => in_array($status, [StatusPeriode::Dinilai, StatusPeriode::Dihitung], true), 'href' => $periode ? route('periode.show', $periode) : route('periode.index')],
            ['label' => 'Hitung MOORA', 'hint' => 'Peringkat & rekomendasi otomatis', 'done' => $status === StatusPeriode::Dihitung, 'href' => $periode ? route('periode.show', $periode) : null],
            ['label' => 'Keputusan pimpinan & finalisasi', 'hint' => $adaFinal ? 'Sudah ada periode final' : 'Dilakukan oleh pimpinan', 'done' => $adaFinal && ! $periode, 'href' => route('hasil.index')],
        ];
    }
}
