<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Kriteria;
use App\Models\PeriodePenilaian;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Data ringkasan dashboard (dipakai ketiga role; Fase 5 dapat memecahnya per role).
 */
final class DashboardData
{
    /**
     * @return array<string, mixed>
     */
    public function for(User $user): array
    {
        $totalAset = Aset::count();
        $perluPerhatian = Aset::perluPerhatian()->count();
        $totalBobot = Kriteria::totalBobotAktifPersen();
        $kriteriaAktif = Kriteria::where('is_active', true)->count();
        $periodeAktif = PeriodePenilaian::aktif()->latest('id')->first();

        return [
            'stats' => [
                ['label' => 'Total aset BMD', 'value' => $totalAset, 'icon' => 'building-office-2', 'tone' => 'brand', 'meta' => KategoriAset::count().' kategori'],
                ['label' => 'Perlu perhatian', 'value' => $perluPerhatian, 'icon' => 'exclamation-triangle', 'tone' => 'amber', 'meta' => 'Sisa UEB ≤ 3 tahun'],
                ['label' => 'Kriteria aktif', 'value' => $kriteriaAktif, 'icon' => 'scale', 'tone' => abs($totalBobot - 100) < 0.0001 ? 'emerald' : 'rose', 'meta' => "Total bobot {$totalBobot}%"],
                $user->isAdmin()
                    ? ['label' => 'Pengguna aktif', 'value' => User::where('is_active', true)->count(), 'icon' => 'users', 'tone' => 'violet', 'meta' => User::count().' akun terdaftar']
                    : ['label' => 'Periode penilaian', 'value' => PeriodePenilaian::count(), 'icon' => 'calendar-days', 'tone' => 'sky', 'meta' => $periodeAktif ? 'Aktif: '.$periodeAktif->status->label() : 'Belum ada periode aktif'],
            ],
            'langkah' => $this->langkah($totalAset, $totalBobot, $kriteriaAktif, $periodeAktif),
            'periodeAktif' => $periodeAktif,
            'asetPerhatian' => Aset::with('kategori')->perluPerhatian()->orderBy('sisa_ueb')->limit(5)->get(),
            'kriteria' => Kriteria::aktif()->get(),
            'totalBobot' => $totalBobot,
            'kategori' => KategoriAset::withCount('aset')->orderByDesc('aset_count')->limit(6)->get(),
            'totalAset' => $totalAset,
        ];
    }

    /**
     * Urutan kerja SPK: data → kriteria → periode → hitung → keputusan/final.
     *
     * @return list<array{label: string, hint: string, done: bool, href: string|null}>
     */
    private function langkah(int $totalAset, float $totalBobot, int $kriteriaAktif, ?PeriodePenilaian $periode): array
    {
        $status = $periode?->status;
        $adaFinal = PeriodePenilaian::where('status', StatusPeriode::Final->value)->exists();

        return [
            ['label' => 'Lengkapi data aset BMD', 'hint' => "{$totalAset} aset tercatat", 'done' => $totalAset >= 2, 'href' => route('aset.index')],
            ['label' => 'Atur kriteria & bobot 100%', 'hint' => "{$kriteriaAktif} kriteria · bobot {$totalBobot}%", 'done' => $kriteriaAktif >= 2 && abs($totalBobot - 100) < 0.0001, 'href' => route('kriteria.index')],
            ['label' => 'Buat periode & input nilai', 'hint' => $periode ? "Periode \"{$periode->nama}\"" : 'Belum ada periode aktif', 'done' => in_array($status, [StatusPeriode::Dinilai, StatusPeriode::Dihitung], true) || $adaFinal, 'href' => $this->url('periode.index')],
            ['label' => 'Hitung MOORA', 'hint' => 'Peringkat & rekomendasi otomatis', 'done' => $status === StatusPeriode::Dihitung || $adaFinal, 'href' => $this->url('hasil.index')],
            ['label' => 'Keputusan pimpinan & finalisasi', 'hint' => $adaFinal ? 'Sudah ada periode final' : 'Menunggu perhitungan', 'done' => $adaFinal, 'href' => $this->url('laporan.index')],
        ];
    }

    private function url(string $route): ?string
    {
        return Route::has($route) ? route($route) : null;
    }
}
