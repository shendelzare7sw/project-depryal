<?php

declare(strict_types=1);

namespace App\Services\Periode;

use App\Models\Aset;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Data tampilan halaman periode & input nilai (query dikumpulkan di sini, bukan di controller/view).
 */
final class PeriodeDetail
{
    /**
     * @param  array<string, mixed>  $filter  q, status (belum|lengkap)
     * @return array<string, mixed>
     */
    public function halaman(PeriodePenilaian $periode, array $filter = []): array
    {
        $kriteria = Kriteria::aktif()->get();
        $aset = $this->asetPeriode($periode, $kriteria->pluck('id')->all());
        $q = mb_strtolower(trim((string) ($filter['q'] ?? '')));
        $status = $filter['status'] ?? null;

        return [
            'periode' => $periode,
            'progres' => $periode->progres(),
            'kriteriaAktif' => $kriteria->count(),
            'totalBobot' => Kriteria::totalBobotAktifPersen(),
            'hambatan' => $this->hambatanHitung($periode, $kriteria),
            'aset' => $aset->filter(fn (Aset $a) => ($q === '' || str_contains(mb_strtolower($a->nama_barang.' '.$a->kode_barang), $q))
                && ($status !== 'lengkap' || $a->getAttribute('nilai_terisi') >= $kriteria->count())
                && ($status !== 'belum' || $a->getAttribute('nilai_terisi') < $kriteria->count()))->values(),
            'totalAset' => $aset->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function penilaian(PeriodePenilaian $periode, Aset $aset): array
    {
        $kriteria = Kriteria::aktif()->with('skala')->get();
        $urutan = $periode->aset()->orderBy('kode_barang')->orderBy('nup')->pluck('aset.id')->all();
        $posisi = (int) array_search($aset->id, $urutan, true);

        return [
            'periode' => $periode,
            'aset' => $aset->load('kategori'),
            'kriteria' => $kriteria,
            'nilai' => NilaiKriteriaAset::where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->pluck('nilai', 'kriteria_id')
                ->map(fn ($n) => (int) round((float) $n))->all(),
            'kondisi' => $periode->aset()->whereKey($aset->id)->first()?->getRelationValue('pivot')?->getAttribute('deskripsi_kondisi'),
            'fotos' => $aset->fotos()->where('periode_id', $periode->id)->get(),
            'posisi' => $posisi + 1,
            'total' => count($urutan),
            'sebelumnya' => $urutan[$posisi - 1] ?? null,
            'berikutnya' => $urutan[$posisi + 1] ?? null,
        ];
    }

    /**
     * Aset pertama (urut kode/NUP) yang nilainya belum lengkap; null bila semua lengkap.
     */
    public function asetBelumLengkapPertama(PeriodePenilaian $periode): ?int
    {
        $jumlahKriteria = Kriteria::where('is_active', true)->count();

        return $this->asetPeriode($periode, Kriteria::where('is_active', true)->pluck('id')->all())
            ->first(fn (Aset $a) => $a->getAttribute('nilai_terisi') < $jumlahKriteria)?->id;
    }

    /**
     * Aset berikutnya (setelah aset ini, berputar) yang nilainya belum lengkap; null bila semua lengkap.
     */
    public function asetBelumLengkapBerikutnya(PeriodePenilaian $periode, Aset $aset): ?int
    {
        $kriteriaIds = Kriteria::where('is_active', true)->pluck('id')->all();
        $semua = $this->asetPeriode($periode, $kriteriaIds);
        $posisi = (int) $semua->search(fn (Aset $a) => $a->id === $aset->id);
        $urut = $semua->slice($posisi + 1)->concat($semua->slice(0, $posisi));

        return $urut->first(fn (Aset $a) => $a->getAttribute('nilai_terisi') < count($kriteriaIds))?->id;
    }

    /**
     * @param  list<int>  $kriteriaIds
     * @return Collection<int, Aset>
     */
    private function asetPeriode(PeriodePenilaian $periode, array $kriteriaIds): Collection
    {
        return $periode->aset()->with('kategori')
            ->withCount([
                'nilaiKriteria as nilai_terisi' => fn (Builder $q) => $q->where('periode_id', $periode->id)->whereIn('kriteria_id', $kriteriaIds),
                'fotos as foto_periode' => fn (Builder $q) => $q->where('periode_id', $periode->id),
            ])
            ->orderBy('kode_barang')->orderBy('nup')->get();
    }

    /**
     * Alasan tombol "Hitung MOORA" dinonaktifkan (kosong = boleh dihitung).
     *
     * @param  Collection<int, Kriteria>  $kriteria
     * @return list<string>
     */
    private function hambatanHitung(PeriodePenilaian $periode, Collection $kriteria): array
    {
        $progres = $periode->progres();
        $bobot = Kriteria::totalBobotAktifPersen();

        return array_values(array_filter([
            $periode->isFinal() ? 'Periode sudah final.' : null,
            $kriteria->count() < 2 ? 'Minimal 2 kriteria aktif.' : null,
            abs($bobot - 100) >= 0.0001 ? "Total bobot kriteria {$bobot}% (harus 100%)." : null,
            $progres['total_aset'] < 2 ? 'Minimal 2 aset dalam periode.' : null,
            $progres['terisi'] < $progres['diperlukan'] ? ($progres['diperlukan'] - $progres['terisi']).' nilai belum diisi.' : null,
        ]));
    }
}
