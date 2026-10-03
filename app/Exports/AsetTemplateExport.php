<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\TeksAmanFormula;
use App\Imports\AsetImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Template Excel import BMD (judul kolom + satu baris contoh).
 * Konstruktor menerima baris lain agar test dapat membuat berkas import dengan format yang sama.
 */
final class AsetTemplateExport implements FromArray, ShouldAutoSize, WithCustomValueBinder, WithHeadings
{
    use TeksAmanFormula;

    /**
     * @param  list<list<mixed>>|null  $rows
     */
    public function __construct(private readonly ?array $rows = null) {}

    /**
     * @return list<list<mixed>>
     */
    public function array(): array
    {
        return $this->rows ?? [[
            'Gedung Kantor Pemerintah', '1.03.01.01.001', 1, 'Gedung Kantor Camat Batuceper', 1, '750,00',
            '15/01/2005', '1.250.000.000,00', '1.250.000.000,00', 50, '525.000.000,00', 29, '725.000.000,00',
            'Jl. Raya Batuceper No. 1',
        ]];
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return AsetImport::HEADINGS;
    }
}
