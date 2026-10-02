<?php

declare(strict_types=1);

namespace App\Services\Moora;

use App\Enums\TipeKriteria;

/**
 * Implementasi metode MOORA (Multi-Objective Optimization on the basis of Ratio Analysis).
 * Pure PHP — tidak ada akses DB, Facade, atau efek samping.
 *
 * Langkah:
 *  1. Penyebut per kriteria: d_j = sqrt(Σ x_ij²). Jika d_j=0 → semua x*_ij = 0.
 *  2. Normalisasi:  x*_ij = x_ij / d_j
 *  3. Terbobot:     v_ij  = w_j * x*_ij
 *  4. Optimasi:     Yi    = Σ(benefit) v_ij − Σ(cost) v_ij
 *  5. Skor relatif: (Yi − Ymin) / (Ymax − Ymin) × 100  [jika Ymax=Ymin → 100 semua]
 *  6. Ranking:      urut Yi menurun; nilai sama → ranking sama (competition ranking 1,2,2,4)
 *
 * Presisi: simpan 6 desimal untuk yi; 2 desimal untuk skor_relatif.
 */
final class MooraCalculator
{
    public function calculate(MooraInput $input): MooraResult
    {
        $criteria = $input->criteria;   // [crit_id => {id, tipe, bobot}]
        $matrix = $input->matrix;     // [aset_id => [crit_id => value]]

        $asetIds = array_keys($matrix);
        $critIds = array_keys($criteria);

        // ── 1. Hitung penyebut per kriteria ──────────────────────────────────
        /** @var array<int, float> $denominators */
        $denominators = [];
        foreach ($critIds as $cj) {
            $sumSq = 0.0;
            foreach ($asetIds as $ai) {
                $val = (float) ($matrix[$ai][$cj] ?? 0.0);
                $sumSq += $val * $val;
            }
            $denominators[$cj] = $sumSq > 0.0 ? sqrt($sumSq) : 0.0;
        }

        // ── 2 & 3. Normalisasi + terbobot ─────────────────────────────────────
        /** @var array<int, array<int, float>> $normalized  [aset_id][crit_id] */
        $normalized = [];
        /** @var array<int, array<int, float>> $weighted    [aset_id][crit_id] */
        $weighted = [];

        foreach ($asetIds as $ai) {
            foreach ($critIds as $cj) {
                $raw = (float) ($matrix[$ai][$cj] ?? 0.0);
                $norm = $denominators[$cj] > 0.0
                    ? $raw / $denominators[$cj]
                    : 0.0;
                $normalized[$ai][$cj] = $norm;
                $weighted[$ai][$cj] = $criteria[$cj]['bobot'] * $norm;
            }
        }

        // ── 4. Optimasi Yi ────────────────────────────────────────────────────
        /** @var array<int, float> $yi */
        $yi = [];
        /** @var array<int, float> $benefitSum */
        $benefitSum = [];
        /** @var array<int, float> $costSum */
        $costSum = [];

        foreach ($asetIds as $ai) {
            $benefit = 0.0;
            $cost = 0.0;
            foreach ($critIds as $cj) {
                $v = $weighted[$ai][$cj];
                if ($criteria[$cj]['tipe'] === TipeKriteria::Benefit) {
                    $benefit += $v;
                } else {
                    $cost += $v;
                }
            }
            $benefitSum[$ai] = $benefit;
            $costSum[$ai] = $cost;
            $yi[$ai] = $benefit - $cost;
        }

        // ── 5. Skor relatif (0–100) ───────────────────────────────────────────
        $yMax = max($yi);
        $yMin = min($yi);
        $diff = $yMax - $yMin;

        /** @var array<int, float> $skorRelatif */
        $skorRelatif = [];
        foreach ($asetIds as $ai) {
            $skorRelatif[$ai] = $diff > 0.0
                ? (($yi[$ai] - $yMin) / $diff) * 100.0
                : 100.0;
        }

        // ── 6. Ranking (competition ranking; urut Yi menurun) ─────────────────
        $sortedIds = $asetIds;
        usort($sortedIds, fn (int $a, int $b): int => $yi[$b] <=> $yi[$a]);

        /** @var array<int, int> $ranking */
        $ranking = [];
        $rank = 1;
        for ($i = 0; $i < count($sortedIds); $i++) {
            $ai = $sortedIds[$i];
            if ($i > 0 && $yi[$ai] === $yi[$sortedIds[$i - 1]]) {
                // Nilai sama → ranking kembar
                $ranking[$ai] = $ranking[$sortedIds[$i - 1]];
            } else {
                $ranking[$ai] = $rank;
            }
            $rank++;
        }

        // ── Susun output ──────────────────────────────────────────────────────
        $rows = [];
        foreach ($asetIds as $ai) {
            $rows[$ai] = [
                'yi' => round($yi[$ai], 6),
                'skor_relatif' => round($skorRelatif[$ai], 2),
                'ranking' => $ranking[$ai],
                'detail' => [
                    'normalized' => $normalized[$ai],
                    'weighted' => $weighted[$ai],
                    'benefit_sum' => round($benefitSum[$ai], 6),
                    'cost_sum' => round($costSum[$ai], 6),
                ],
            ];
        }

        return new MooraResult($rows);
    }
}
