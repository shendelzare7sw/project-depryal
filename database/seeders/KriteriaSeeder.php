<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TipeKriteria;
use App\Models\Kriteria;
use App\Models\KriteriaSkala;
use Illuminate\Database\Seeder;

class KriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $kriteriaList = [
            [
                'kode' => 'C1',
                'nama' => 'Fungsi Aset',
                'tipe' => TipeKriteria::Benefit,
                'bobot' => 0.40,
                'skala_min' => 1,
                'skala_maks' => 5,
                'urutan' => 1,
                'is_active' => true,
                'keterangan' => 'Kesesuaian dan optimalisasi fungsi operasional gedung/bangunan untuk pelayanan publik.',
                'skalas' => [
                    ['nilai' => 1, 'label' => 'Sangat Buruk', 'deskripsi' => 'Aset rusak total, tidak dapat digunakan atau membahayakan.'],
                    ['nilai' => 2, 'label' => 'Buruk', 'deskripsi' => 'Fungsi terganggu berat, membutuhkan perbaikan struktural besar.'],
                    ['nilai' => 3, 'label' => 'Cukup', 'deskripsi' => 'Fungsi berjalan terbatas/sebagian, terdapat kerusakan sedang.'],
                    ['nilai' => 4, 'label' => 'Baik', 'deskripsi' => 'Fungsi berjalan normal dengan kendala atau kerusakan ringan.'],
                    ['nilai' => 5, 'label' => 'Sangat Baik', 'deskripsi' => 'Fungsi optimal 100%, kondisi sangat prima tanpa keluhan.'],
                ],
            ],
            [
                'kode' => 'C2',
                'nama' => 'Efektivitas Pemanfaatan',
                'tipe' => TipeKriteria::Benefit,
                'bobot' => 0.35,
                'skala_min' => 1,
                'skala_maks' => 5,
                'urutan' => 2,
                'is_active' => true,
                'keterangan' => 'Tingkat frekuensi dan intensitas penggunaan aset oleh aparatur kecamatan atau masyarakat.',
                'skalas' => [
                    ['nilai' => 1, 'label' => 'Sangat Rendah', 'deskripsi' => 'Jarang sekali dipakai atau terbengkalai (utilisasi < 20%).'],
                    ['nilai' => 2, 'label' => 'Rendah', 'deskripsi' => 'Hanya digunakan sesekali/insidental (utilisasi 20% - 40%).'],
                    ['nilai' => 3, 'label' => 'Sedang', 'deskripsi' => 'Digunakan terjadwal berkala beberapa kali seminggu (utilisasi 41% - 60%).'],
                    ['nilai' => 4, 'label' => 'Tinggi', 'deskripsi' => 'Digunakan rutin setiap hari kerja (utilisasi 61% - 80%).'],
                    ['nilai' => 5, 'label' => 'Sangat Tinggi', 'deskripsi' => 'Digunakan terus menerus setiap hari kerja dan akhir pekan (utilisasi > 80%).'],
                ],
            ],
            [
                'kode' => 'C3',
                'nama' => 'Biaya Pemeliharaan',
                'tipe' => TipeKriteria::Cost,
                'bobot' => 0.25,
                'skala_min' => 1,
                'skala_maks' => 5,
                'urutan' => 3,
                'is_active' => true,
                'keterangan' => 'Beban anggaran yang harus dikeluarkan untuk perawatan dan perbaikan rutin tahunan.',
                'skalas' => [
                    ['nilai' => 1, 'label' => 'Sangat Rendah', 'deskripsi' => 'Biaya pemeliharaan sangat efisien (< 5% nilai perolehan per tahun).'],
                    ['nilai' => 2, 'label' => 'Rendah', 'deskripsi' => 'Biaya pemeliharaan wajar (5% - 15% nilai perolehan per tahun).'],
                    ['nilai' => 3, 'label' => 'Sedang', 'deskripsi' => 'Biaya pemeliharaan cukup tinggi (16% - 30% nilai perolehan per tahun).'],
                    ['nilai' => 4, 'label' => 'Tinggi', 'deskripsi' => 'Biaya pemeliharaan membebani anggaran (31% - 50% nilai perolehan per tahun).'],
                    ['nilai' => 5, 'label' => 'Sangat Tinggi', 'deskripsi' => 'Biaya pemeliharaan tidak ekonomis lagi (> 50% nilai perolehan per tahun).'],
                ],
            ],
        ];

        foreach ($kriteriaList as $item) {
            $skalas = $item['skalas'];
            unset($item['skalas']);

            $kriteria = Kriteria::updateOrCreate(['kode' => $item['kode']], $item);

            foreach ($skalas as $skala) {
                KriteriaSkala::updateOrCreate(
                    [
                        'kriteria_id' => $kriteria->id,
                        'nilai' => $skala['nilai'],
                    ],
                    $skala
                );
            }
        }
    }
}
