<?php

declare(strict_types=1);

namespace App\Actions\Pengaturan;

use App\Enums\ModulAudit;
use App\Models\User;
use App\Support\Setting;

final class SimpanPengaturan
{
    /**
     * Simpan pengaturan (key-value) dan catat perubahan sebelum/sesudah di audit log.
     * Ambang baru hanya berlaku untuk perhitungan berikutnya (periode lama memakai snapshot).
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, User $by): void
    {
        $lama = Setting::all();
        $berubah = [];

        foreach ($data as $key => $value) {
            $baru = $value === null ? null : (string) $value;

            if (($lama[$key] ?? null) !== $baru) {
                $berubah[$key] = ['lama' => $lama[$key] ?? null, 'baru' => $baru];
                Setting::set($key, $baru);
            }
        }

        if ($berubah !== []) {
            activity(ModulAudit::Pengaturan->value)->causedBy($by)
                ->withProperties([
                    'attributes' => array_map(fn (array $v) => $v['baru'], $berubah),
                    'old' => array_map(fn (array $v) => $v['lama'], $berubah),
                ])
                ->log('Pengaturan diubah: '.implode(', ', array_keys($berubah)));
        }
    }
}
