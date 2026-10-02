<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\KategoriAset;
use Illuminate\Database\Seeder;

class KategoriAsetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['kode' => 'GB-KTR', 'nama' => 'Gedung Kantor Pemerintah'],
            ['kode' => 'GB-SBG', 'nama' => 'Gedung Pertemuan / Serbaguna'],
            ['kode' => 'GB-KSH', 'nama' => 'Gedung Posyandu & Kesehatan'],
            ['kode' => 'GB-PDK', 'nama' => 'Gedung Pendidikan / Paud'],
            ['kode' => 'GB-KMN', 'nama' => 'Bangunan Pos Pengamanan / Jaga'],
            ['kode' => 'GB-INF', 'nama' => 'Bangunan Air / Fasilitas Umum'],
        ];

        foreach ($categories as $cat) {
            KategoriAset::updateOrCreate(['kode' => $cat['kode']], $cat);
        }
    }
}
