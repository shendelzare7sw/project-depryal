<?php

declare(strict_types=1);

namespace App\Services\Moora;

/**
 * Immutable output dari MooraCalculator.
 *
 * @param array<int, array{
 *     yi: float,
 *     skor_relatif: float,
 *     ranking: int,
 *     detail: array{
 *         normalized: array<int, float>,
 *         weighted: array<int, float>,
 *         benefit_sum: float,
 *         cost_sum: float,
 *     }
 * }> $rows  Keyed by aset_id.
 */
final readonly class MooraResult
{
    /**
     * @param array<int, array{
     *     yi: float,
     *     skor_relatif: float,
     *     ranking: int,
     *     detail: array{
     *         normalized: array<int, float>,
     *         weighted: array<int, float>,
     *         benefit_sum: float,
     *         cost_sum: float,
     *     }
     * }> $rows
     */
    public function __construct(
        public array $rows,
    ) {}
}
