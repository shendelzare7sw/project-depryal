<?php

declare(strict_types=1);

namespace App\Enums;

enum TipeKriteria: string
{
    case Benefit = 'benefit';
    case Cost = 'cost';

    public function label(): string
    {
        return match ($this) {
            self::Benefit => 'Benefit (Keuntungan)',
            self::Cost => 'Cost (Biaya)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Benefit => 'success',
            self::Cost => 'warning',
        };
    }

    /**
     * @return array<string, string> value => label, untuk <x-form.select>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
