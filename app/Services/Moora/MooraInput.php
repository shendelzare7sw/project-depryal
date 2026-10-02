<?php

declare(strict_types=1);

namespace App\Services\Moora;

use App\Enums\TipeKriteria;

/**
 * Immutable input untuk MooraCalculator.
 *
 * @param  array<int, array{id: int, tipe: TipeKriteria, bobot: float}>  $criteria
 *                                                                                  Keyed by kriteria_id.
 * @param  array<int, array<int, float>>  $matrix
 *                                                 Keyed by [aset_id][kriteria_id] = nilai (float).
 */
final readonly class MooraInput
{
    /**
     * @param  array<int, array{id: int, tipe: TipeKriteria, bobot: float}>  $criteria
     * @param  array<int, array<int, float>>  $matrix
     */
    public function __construct(
        public array $criteria,
        public array $matrix,
    ) {}
}
