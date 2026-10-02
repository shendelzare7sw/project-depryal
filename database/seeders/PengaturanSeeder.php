<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\Support\Setting;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'ambang_pertahankan' => '66.67',
            'ambang_perbaiki' => '33.33',
            'nama_instansi' => 'Pemerintah Kota Tangerang - Kecamatan Batuceper',
            'nama_penandatangan' => 'H. Mulyadi, S.Sos., M.Si.',
            'nip_penandatangan' => '197105021992031004',
            'jabatan_penandatangan' => 'Camat Batuceper',
        ];

        foreach ($settings as $key => $value) {
            Pengaturan::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::clearCache();
    }
}
