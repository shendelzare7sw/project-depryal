<?php

declare(strict_types=1);

namespace App\Imports;

/**
 * Hasil baca + validasi berkas Excel BMD (dipakai untuk halaman pratinjau & penyimpanan).
 */
final readonly class AsetImportResult
{
    /**
     * @param  list<array{baris: int, data: array<string, mixed>}>  $valid
     * @param  list<array{baris: int, data: array<string, mixed>, errors: list<string>}>  $invalid
     */
    public function __construct(
        public array $valid,
        public array $invalid,
    ) {}

    public function total(): int
    {
        return count($this->valid) + count($this->invalid);
    }
}
