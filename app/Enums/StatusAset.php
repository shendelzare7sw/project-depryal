<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusAset: string
{
    case Aktif = 'aktif';
    case DalamPerbaikan = 'dalam_perbaikan';
    case DiusulkanHapus = 'diusulkan_hapus';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::DalamPerbaikan => 'Dalam Perbaikan',
            self::DiusulkanHapus => 'Diusulkan Hapus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Aktif => 'success',
            self::DalamPerbaikan => 'warning',
            self::DiusulkanHapus => 'error',
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
