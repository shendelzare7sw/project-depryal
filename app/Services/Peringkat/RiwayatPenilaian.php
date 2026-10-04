<?php

declare(strict_types=1);

namespace App\Services\Peringkat;

use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\PeriodePenilaian;
use Illuminate\Support\Collection;

/**
 * Riwayat hasil MOORA lintas periode: per aset (detail aset) dan perbandingan dua periode (tren peringkat).
 */
final class RiwayatPenilaian
{
    /**
     * Hasil setiap periode yang memuat aset ini, terbaru (tanggal mulai) di atas.
     *
     * @return Collection<int, array{periode: PeriodePenilaian, hasil: HasilMoora, total: int, keputusan: Keputusan|null}>
     */
    public function perAset(Aset $aset): Collection
    {
        $hasil = HasilMoora::with('periode')->where('aset_id', $aset->id)->get();
        $total = HasilMoora::whereIn('periode_id', $hasil->pluck('periode_id'))
            ->selectRaw('periode_id, COUNT(*) as jumlah')->groupBy('periode_id')->pluck('jumlah', 'periode_id');
        $keputusan = Keputusan::where('aset_id', $aset->id)->get()->keyBy('periode_id');
        $riwayat = collect();

        foreach ($hasil as $h) {
            if ($h->periode instanceof PeriodePenilaian) {
                $riwayat->push([
                    'periode' => $h->periode,
                    'hasil' => $h,
                    'total' => (int) ($total[$h->periode_id] ?? 0),
                    'keputusan' => $keputusan->get($h->periode_id),
                ]);
            }
        }

        return $riwayat->sortByDesc(fn (array $r) => [$r['periode']->tanggal_mulai?->format('Ymd'), $r['periode']->id])->values();
    }

    /**
     * Periode yang punya hasil MOORA (pilihan perbandingan), terbaru di atas.
     *
     * @return Collection<int, PeriodePenilaian>
     */
    public function periodeBerhasil(): Collection
    {
        return PeriodePenilaian::whereIn('status', [StatusPeriode::Dihitung->value, StatusPeriode::Final->value])
            ->orderByDesc('tanggal_mulai')->orderByDesc('id')->get();
    }

    /**
     * Bandingkan peringkat aset pada periode lama vs baru. Selisih positif = peringkat naik (membaik).
     *
     * @return array{baris: Collection<int, array{aset: Aset, lama: HasilMoora|null, baru: HasilMoora|null, naik: int|null, selisih_skor: float|null, berubah: bool}>, ringkasan: array{sama: int, naik: int, turun: int, baru: int, keluar: int, rekomendasi_berubah: int}}
     */
    public function perbandingan(PeriodePenilaian $lama, PeriodePenilaian $baru): array
    {
        $hasilLama = $lama->hasilMoora()->with(['aset' => fn ($q) => $q->withTrashed()])->get()->keyBy('aset_id');
        $hasilBaru = $baru->hasilMoora()->with(['aset' => fn ($q) => $q->withTrashed()])->get()->keyBy('aset_id');

        $baris = $hasilBaru->keys()->merge($hasilLama->keys())->unique()->map(function (int $asetId) use ($hasilLama, $hasilBaru) {
            $l = $hasilLama->get($asetId);
            $b = $hasilBaru->get($asetId);

            return [
                'aset' => ($b ?? $l)?->aset,
                'lama' => $l,
                'baru' => $b,
                'naik' => $l && $b ? $l->ranking - $b->ranking : null,
                'selisih_skor' => $l && $b ? round((float) $b->skor_relatif - (float) $l->skor_relatif, 2) : null,
                'berubah' => $l && $b && $l->rekomendasi !== $b->rekomendasi,
            ];
        })->filter(fn (array $r) => $r['aset'] !== null)
            ->sortBy(fn (array $r) => $r['baru']->ranking ?? PHP_INT_MAX)->values();

        return [
            'baris' => $baris,
            'ringkasan' => [
                'sama' => $baris->where('naik', 0)->count(),
                'naik' => $baris->filter(fn ($r) => ($r['naik'] ?? 0) > 0)->count(),
                'turun' => $baris->filter(fn ($r) => ($r['naik'] ?? 0) < 0)->count(),
                'baru' => $baris->whereNull('lama')->count(),
                'keluar' => $baris->whereNull('baru')->count(),
                'rekomendasi_berubah' => $baris->where('berubah', true)->count(),
            ],
        ];
    }
}
