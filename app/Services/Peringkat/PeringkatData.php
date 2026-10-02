<?php

declare(strict_types=1);

namespace App\Services\Peringkat;

use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\Kriteria;
use App\Models\KriteriaSkala;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use Illuminate\Support\Collection;

/**
 * Data tampilan peringkat, detail aset, keputusan, dan detail perhitungan (query terkumpul di sini).
 */
final class PeringkatData
{
    /**
     * @param  array<string, mixed>  $filter  q, rekomendasi, keputusan (sudah|belum)
     * @return array<string, mixed>
     */
    public function index(PeriodePenilaian $periode, array $filter = []): array
    {
        $semua = $this->baris($periode);
        $q = mb_strtolower(trim((string) ($filter['q'] ?? '')));
        $rekomendasi = TindakanAset::tryFrom((string) ($filter['rekomendasi'] ?? ''));
        $keputusan = $filter['keputusan'] ?? null;
        $diputuskan = $semua->whereNotNull('keputusan')->count();

        return [
            'periode' => $periode,
            'baris' => $semua->filter(fn (array $b) => ($q === '' || str_contains(mb_strtolower($b['aset']->nama_barang.' '.$b['aset']->kode_barang), $q))
                && (! $rekomendasi || $b['hasil']->rekomendasi === $rekomendasi)
                && ($keputusan !== 'sudah' || $b['keputusan'] !== null)
                && ($keputusan !== 'belum' || $b['keputusan'] === null))->values(),
            'total' => $semua->count(),
            'diputuskan' => $diputuskan,
            'ringkasan' => collect(TindakanAset::cases())->mapWithKeys(fn (TindakanAset $t) => [$t->value => $semua->filter(fn ($b) => $b['hasil']->rekomendasi === $t)->count()])->all(),
            'bisaFinalisasi' => $periode->status === StatusPeriode::Dihitung && $semua->isNotEmpty() && $diputuskan === $semua->count(),
            'belumPertama' => $semua->first(fn (array $b) => $b['keputusan'] === null)['aset'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function aset(PeriodePenilaian $periode, Aset $aset): array
    {
        $hasil = HasilMoora::where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->firstOrFail();
        $kriteria = $this->kriteriaSnapshot($periode);
        $nilai = NilaiKriteriaAset::where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->pluck('nilai', 'kriteria_id')->all();
        $label = KriteriaSkala::whereIn('kriteria_id', $kriteria->pluck('id'))->get()->groupBy('kriteria_id');
        $urutan = HasilMoora::where('periode_id', $periode->id)->orderBy('ranking')->orderBy('id')->pluck('aset_id')->all();
        $posisi = (int) array_search($aset->id, $urutan, true);

        return [
            'periode' => $periode,
            'aset' => $aset->load('kategori'),
            'hasil' => $hasil,
            'keputusan' => Keputusan::with('user')->where(['periode_id' => $periode->id, 'aset_id' => $aset->id])->first(),
            'kriteria' => $kriteria->map(fn (array $k) => $k + [
                'nilai' => isset($nilai[$k['id']]) ? (float) $nilai[$k['id']] : null,
                'label' => $label->get($k['id'])?->firstWhere('nilai', (int) round((float) ($nilai[$k['id']] ?? 0)))?->label,
                'normalized' => (float) ($hasil->detail['normalized'][$k['id']] ?? 0),
                'weighted' => (float) ($hasil->detail['weighted'][$k['id']] ?? 0),
            ]),
            'peringatan' => (new PeringatanBiayaTinggi(Kriteria::all()))->berlaku($nilai),
            'kondisi' => $periode->aset()->whereKey($aset->id)->first()?->getRelationValue('pivot')?->getAttribute('deskripsi_kondisi'),
            'fotos' => $aset->fotos()->where('periode_id', $periode->id)->get(),
            'total' => count($urutan),
            'sebelumnya' => $urutan[$posisi - 1] ?? null,
            'berikutnya' => $urutan[$posisi + 1] ?? null,
        ];
    }

    /**
     * Aset belum diputuskan berikutnya (urut ranking), null bila semua sudah.
     */
    public function belumDiputuskanBerikutnya(PeriodePenilaian $periode): ?int
    {
        return HasilMoora::where('periode_id', $periode->id)
            ->whereNotIn('aset_id', Keputusan::where('periode_id', $periode->id)->select('aset_id'))
            ->orderBy('ranking')->orderBy('id')->value('aset_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function perhitungan(PeriodePenilaian $periode): array
    {
        $kriteria = $this->kriteriaSnapshot($periode);
        $hasil = HasilMoora::with(['aset' => fn ($q) => $q->withTrashed()])->where('periode_id', $periode->id)->orderBy('ranking')->orderBy('id')->get();
        $matriks = NilaiKriteriaAset::where('periode_id', $periode->id)->get()
            ->groupBy('aset_id')->map(fn (Collection $n) => $n->pluck('nilai', 'kriteria_id')->map(fn ($v) => (float) $v));

        // Penyebut √Σx² diturunkan dari hasil tersimpan (x / x*) — tidak menghitung ulang MOORA.
        $penyebut = $kriteria->mapWithKeys(function (array $k) use ($hasil, $matriks) {
            $baris = $hasil->first(fn (HasilMoora $h) => (float) ($h->detail['normalized'][$k['id']] ?? 0) > 0);

            return [$k['id'] => $baris ? (float) $matriks[$baris->aset_id][$k['id']] / (float) $baris->detail['normalized'][$k['id']] : 0.0];
        });

        return compact('periode', 'kriteria', 'hasil', 'matriks', 'penyebut');
    }

    /**
     * @return Collection<int, array{hasil: HasilMoora, aset: Aset, keputusan: Keputusan|null, peringatan: bool}>
     */
    private function baris(PeriodePenilaian $periode): Collection
    {
        $keputusan = Keputusan::where('periode_id', $periode->id)->get()->keyBy('aset_id');
        $nilai = NilaiKriteriaAset::where('periode_id', $periode->id)->get()->groupBy('aset_id');
        $peringatan = new PeringatanBiayaTinggi(Kriteria::all());

        $hasil = HasilMoora::with(['aset' => fn ($q) => $q->withTrashed()->with('kategori')])
            ->where('periode_id', $periode->id)->orderBy('ranking')->orderBy('id')->get();
        $baris = collect();

        foreach ($hasil as $h) {
            $aset = $h->aset;

            if ($aset === null) {
                continue;
            }

            $baris->push([
                'hasil' => $h,
                'aset' => $aset,
                'keputusan' => $keputusan->get($h->aset_id),
                'peringatan' => $peringatan->berlaku($nilai->get($h->aset_id)?->pluck('nilai', 'kriteria_id')->all() ?? []),
            ]);
        }

        return $baris;
    }

    /**
     * Kriteria sesuai snapshot saat dihitung (bobot historis), fallback kriteria aktif.
     *
     * @return Collection<int, array{id: int, kode: string, nama: string, tipe: string, bobot: float}>
     */
    private function kriteriaSnapshot(PeriodePenilaian $periode): Collection
    {
        $snapshot = $periode->snapshot_kriteria
            ?? Kriteria::aktif()->get()->map(fn (Kriteria $k) => ['id' => $k->id, 'kode' => $k->kode, 'nama' => $k->nama, 'tipe' => $k->tipe->value, 'bobot' => $k->bobot])->all();

        return collect($snapshot)->map(fn (array $k) => [
            'id' => (int) $k['id'], 'kode' => (string) $k['kode'], 'nama' => (string) $k['nama'], 'tipe' => (string) $k['tipe'], 'bobot' => (float) $k['bobot'],
        ])->values();
    }
}
