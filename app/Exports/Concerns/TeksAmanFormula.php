<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Pencegah formula/CSV injection: teks yang diawali = + - @ ditulis sebagai string,
 * bukan formula, sehingga isian pengguna (mis. nama barang) tidak dieksekusi Excel.
 * Pakai bersama interface WithCustomValueBinder.
 */
trait TeksAmanFormula
{
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) && ! is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return (new DefaultValueBinder)->bindValue($cell, $value);
    }
}
