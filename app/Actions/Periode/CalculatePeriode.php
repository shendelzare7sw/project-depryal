<?php

declare(strict_types=1);

namespace App\Actions\Periode;

use App\Enums\StatusPeriode;
use App\Enums\TipeKriteria;
use App\Models\HasilMoora;
use App\Models\Keputusan;
use App\Models\Kriteria;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\Moora\MooraCalculator;
use App\Services\Moora\MooraInput;
use App\Services\Recommendation\RecommendationResolver;
use App\Support\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Action: Hitung MOORA untuk satu periode dan simpan hasil ke DB.
 *
 * Dilempar DomainException (pesan Indonesia) jika:
 *   - Periode sudah final
 *   - Bobot kriteria aktif ≠ 1,00 (toleransi 0,0001)
 *   - Kurang dari 2 kriteria aktif
 *   - Kurang dari 2 aset dalam periode
 *   - Ada nilai yang belum diisi (nilai tidak lengkap)
 *
 * Saat hitung ulang: hasil_moora & keputusan lama periode ini dihapus dulu.
 */
final class CalculatePeriode
{
    public function __construct(
        private readonly MooraCalculator $calculator,
    ) {}

    public function execute(PeriodePenilaian $periode, User $by): void
    {
        // ── Guard: periode tidak boleh final ──────────────────────────────────
        if ($periode->status === StatusPeriode::Final) {
            throw new \DomainException('Periode sudah final dan tidak dapat dihitung ulang.');
        }

        // ── Ambil kriteria aktif ───────────────────────────────────────────────
        $kriteriaAktif = Kriteria::aktif()->get();

        if ($kriteriaAktif->count() < 2) {
            throw new \DomainException('Minimal 2 kriteria aktif diperlukan untuk menghitung MOORA.');
        }

        // ── Validasi bobot = 1,00 (±0,0001) ──────────────────────────────────
        $totalBobot = $kriteriaAktif->sum('bobot');
        if (abs($totalBobot - 1.0) > 0.0001) {
            $pct = number_format($totalBobot * 100, 2, ',', '.');
            throw new \DomainException(
                "Total bobot kriteria aktif adalah {$pct}% (harus 100%). Sesuaikan bobot terlebih dahulu."
            );
        }

        // ── Ambil aset dalam periode ──────────────────────────────────────────
        $asetIds = $periode->aset()->pluck('aset.id')->all();

        if (count($asetIds) < 2) {
            throw new \DomainException('Minimal 2 aset diperlukan dalam periode untuk menghitung MOORA.');
        }

        // ── Validasi nilai lengkap ────────────────────────────────────────────
        $critIds = $kriteriaAktif->pluck('id')->all();
        $nilaiTersedia = NilaiKriteriaAset::where('periode_id', $periode->id)
            ->whereIn('aset_id', $asetIds)
            ->whereIn('kriteria_id', $critIds)
            ->count();

        $nilaiDiperlukan = count($asetIds) * count($critIds);

        if ($nilaiTersedia < $nilaiDiperlukan) {
            $kurang = $nilaiDiperlukan - $nilaiTersedia;
            throw new \DomainException(
                "Masih ada {$kurang} nilai yang belum diisi. Lengkapi semua nilai sebelum menghitung."
            );
        }

        // ── Bangun matriks [aset_id][kriteria_id] = nilai ────────────────────
        $nilaiRows = NilaiKriteriaAset::where('periode_id', $periode->id)
            ->whereIn('aset_id', $asetIds)
            ->whereIn('kriteria_id', $critIds)
            ->get(['aset_id', 'kriteria_id', 'nilai']);

        /** @var array<int, array<int, float>> $matrix */
        $matrix = [];
        foreach ($nilaiRows as $row) {
            $matrix[$row->aset_id][$row->kriteria_id] = (float) $row->nilai;
        }

        /** @var array<int, array{id: int, tipe: TipeKriteria, bobot: float}> $criteriaMap */
        $criteriaMap = [];
        foreach ($kriteriaAktif as $k) {
            $criteriaMap[$k->id] = [
                'id' => $k->id,
                'tipe' => $k->tipe,
                'bobot' => (float) $k->bobot,
            ];
        }

        $input = new MooraInput($criteriaMap, $matrix);

        // ── Hitung MOORA ──────────────────────────────────────────────────────
        $result = $this->calculator->calculate($input);

        // ── Resolver rekomendasi ──────────────────────────────────────────────
        $resolver = RecommendationResolver::fromSetting();

        // ── Snapshot data saat dihitung ───────────────────────────────────────
        $snapshotKriteria = [];
        foreach ($kriteriaAktif as $kriteriaItem) {
            /** @var Kriteria $kriteriaItem */
            $snapshotKriteria[] = [
                'id' => $kriteriaItem->id,
                'kode' => $kriteriaItem->kode,
                'nama' => $kriteriaItem->nama,
                'tipe' => $kriteriaItem->tipe->value,
                'bobot' => $kriteriaItem->bobot,
            ];
        }

        $snapshotAmbang = [
            'pertahankan' => Setting::ambangPertahankan(),
            'perbaiki' => Setting::ambangPerbaiki(),
        ];

        // ── Transaksi DB: hapus lama + simpan baru ────────────────────────────
        DB::transaction(function () use ($periode, $by, $result, $resolver, $snapshotKriteria, $snapshotAmbang): void {
            // Hapus hasil & keputusan lama periode ini
            HasilMoora::where('periode_id', $periode->id)->delete();
            Keputusan::where('periode_id', $periode->id)->delete();

            // Simpan hasil baru
            foreach ($result->rows as $asetId => $row) {
                HasilMoora::create([
                    'periode_id' => $periode->id,
                    'aset_id' => $asetId,
                    'yi' => $row['yi'],
                    'skor_relatif' => $row['skor_relatif'],
                    'ranking' => $row['ranking'],
                    'rekomendasi' => $resolver->resolve($row['skor_relatif']),
                    'detail' => $row['detail'],
                ]);
            }

            // Perbarui periode
            $periode->update([
                'status' => StatusPeriode::Dihitung,
                'snapshot_kriteria' => $snapshotKriteria,
                'snapshot_ambang' => $snapshotAmbang,
                'dihitung_pada' => now(),
                'dihitung_oleh' => $by->id,
            ]);
        });
    }
}
