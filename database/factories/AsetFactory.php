<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StatusAset;
use App\Models\Aset;
use App\Models\KategoriAset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aset>
 */
class AsetFactory extends Factory
{
    public function definition(): array
    {
        $hargaSatuan = fake()->numberBetween(50_000_000, 1_500_000_000);
        $umurEkonomis = fake()->numberBetween(10, 50);
        $sisaUeb = fake()->numberBetween(1, $umurEkonomis);
        $persenPenyusutan = ($umurEkonomis - $sisaUeb) / $umurEkonomis;
        $akumulasiPenyusutan = round($hargaSatuan * $persenPenyusutan);
        $nilaiBuku = $hargaSatuan - $akumulasiPenyusutan;

        return [
            'kategori_aset_id' => KategoriAset::factory(),
            'kode_barang' => '01.01.11.'.fake()->numerify('##.##.###'),
            'nup' => fake()->unique()->numberBetween(1, 99999),
            'nama_barang' => fake()->randomElement([
                'Gedung Kantor Camat Batuceper',
                'Gedung Serbaguna Batuceper',
                'Posyandu Mawar Kelurahan Kebon Besar',
                'Posyandu Melati Kelurahan Poris Gaga',
                'Gedung PKK Kecamatan Batuceper',
                'Rumah Dinas Camat Batuceper',
                'Gedung Aula Pertemuan Batusari',
                'Bangunan Pos Keamanan Poris Gaga Baru',
            ]),
            'jumlah' => 1,
            'luas' => fake()->randomFloat(2, 50, 1500),
            'tanggal_perolehan' => fake()->dateTimeBetween('-20 years', '-1 years')->format('Y-m-d'),
            'harga_satuan' => $hargaSatuan,
            'nilai_perolehan' => $hargaSatuan,
            'umur_ekonomis' => $umurEkonomis,
            'akumulasi_penyusutan' => $akumulasiPenyusutan,
            'sisa_ueb' => $sisaUeb,
            'nilai_buku' => $nilaiBuku,
            'lokasi' => 'Kecamatan Batuceper, Kota Tangerang',
            'status' => StatusAset::Aktif,
        ];
    }
}
