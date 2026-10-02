<?php

declare(strict_types=1);

namespace App\Support;

class Rupiah
{
    public static function format(float|int|string|null $value, bool $withPrefix = true): string
    {
        if ($value === null || $value === '') {
            return $withPrefix ? 'Rp 0' : '0';
        }

        $numeric = (float) $value;
        $formatted = number_format($numeric, 2, ',', '.');

        // Jika dua desimal di belakang adalah ,00, boleh dibersihkan atau tetap standar akuntansi
        // Standar BMD: format dengan 2 desimal atau tanpa desimal jika bulat
        if (str_ends_with($formatted, ',00')) {
            $formatted = substr($formatted, 0, -3);
        }

        return $withPrefix ? 'Rp '.$formatted : $formatted;
    }
}
