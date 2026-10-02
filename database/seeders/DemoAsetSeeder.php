<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\StatusAset;
use App\Models\Aset;
use App\Models\KategoriAset;
use Illuminate\Database\Seeder;

class DemoAsetSeeder extends Seeder
{
    public function run(): void
    {
        $asetDummy = [
            ['nama_barang' => 'Gedung Kantor Camat Batuceper', 'kode_barang' => '01.01.11.01.01.001', 'nup' => 1, 'luas' => 750.00, 'th_perolehan' => '2005-01-01', 'harga' => 1_250_000_000, 'umur' => 50, 'sisa' => 29, 'lokasi' => 'Jl. Raya Batuceper No. 1', 'kat' => 'GB-KTR'],
            ['nama_barang' => 'Aula Pertemuan Kecamatan Batuceper', 'kode_barang' => '01.01.11.01.01.002', 'nup' => 1, 'luas' => 400.00, 'th_perolehan' => '2008-03-01', 'harga' => 650_000_000, 'umur' => 40, 'sisa' => 22, 'lokasi' => 'Jl. Raya Batuceper No. 1', 'kat' => 'GB-SBG'],
            ['nama_barang' => 'Posyandu Mawar Kelurahan Kebon Besar', 'kode_barang' => '01.01.11.02.01.001', 'nup' => 1, 'luas' => 120.00, 'th_perolehan' => '2010-06-01', 'harga' => 180_000_000, 'umur' => 30, 'sisa' => 14, 'lokasi' => 'Kel. Kebon Besar', 'kat' => 'GB-KSH'],
            ['nama_barang' => 'Posyandu Melati Kelurahan Poris Gaga', 'kode_barang' => '01.01.11.02.01.002', 'nup' => 1, 'luas' => 110.00, 'th_perolehan' => '2011-01-01', 'harga' => 175_000_000, 'umur' => 30, 'sisa' => 15, 'lokasi' => 'Kel. Poris Gaga', 'kat' => 'GB-KSH'],
            ['nama_barang' => 'Posyandu Anggrek Kelurahan Batuceper', 'kode_barang' => '01.01.11.02.01.003', 'nup' => 1, 'luas' => 95.00, 'th_perolehan' => '2012-07-01', 'harga' => 160_000_000, 'umur' => 30, 'sisa' => 17, 'lokasi' => 'Kel. Batuceper', 'kat' => 'GB-KSH'],
            ['nama_barang' => 'Gedung PKK Kecamatan Batuceper', 'kode_barang' => '01.01.11.01.02.001', 'nup' => 1, 'luas' => 200.00, 'th_perolehan' => '2009-04-01', 'harga' => 320_000_000, 'umur' => 35, 'sisa' => 18, 'lokasi' => 'Jl. Raya Batuceper No. 3', 'kat' => 'GB-SBG'],
            ['nama_barang' => 'Gedung PAUD Melati Kelurahan Batujaya', 'kode_barang' => '01.01.11.03.01.001', 'nup' => 1, 'luas' => 150.00, 'th_perolehan' => '2015-08-01', 'harga' => 280_000_000, 'umur' => 25, 'sisa' => 14, 'lokasi' => 'Kel. Batujaya', 'kat' => 'GB-PDK'],
            ['nama_barang' => 'Gedung PAUD Cempaka Kelurahan Poris Gaga', 'kode_barang' => '01.01.11.03.01.002', 'nup' => 1, 'luas' => 140.00, 'th_perolehan' => '2016-02-01', 'harga' => 260_000_000, 'umur' => 25, 'sisa' => 15, 'lokasi' => 'Kel. Poris Gaga', 'kat' => 'GB-PDK'],
            ['nama_barang' => 'Pos Pengamanan Kelurahan Kebon Besar', 'kode_barang' => '01.01.11.04.01.001', 'nup' => 1, 'luas' => 20.00, 'th_perolehan' => '2013-11-01', 'harga' => 85_000_000, 'umur' => 20, 'sisa' => 7, 'lokasi' => 'Kel. Kebon Besar', 'kat' => 'GB-KMN'],
            ['nama_barang' => 'Pos Pengamanan Kelurahan Batuceper', 'kode_barang' => '01.01.11.04.01.002', 'nup' => 1, 'luas' => 22.00, 'th_perolehan' => '2014-05-01', 'harga' => 90_000_000, 'umur' => 20, 'sisa' => 8, 'lokasi' => 'Kel. Batuceper', 'kat' => 'GB-KMN'],
            ['nama_barang' => 'Bangunan Saluran Drainase Primer Batuceper', 'kode_barang' => '01.01.11.05.01.001', 'nup' => 1, 'luas' => 600.00, 'th_perolehan' => '2007-09-01', 'harga' => 950_000_000, 'umur' => 40, 'sisa' => 21, 'lokasi' => 'Kec. Batuceper', 'kat' => 'GB-INF'],
            ['nama_barang' => 'Posyandu Dahlia Kelurahan Poris Gaga Baru', 'kode_barang' => '01.01.11.02.01.004', 'nup' => 1, 'luas' => 105.00, 'th_perolehan' => '2013-03-01', 'harga' => 168_000_000, 'umur' => 30, 'sisa' => 17, 'lokasi' => 'Kel. Poris Gaga Baru', 'kat' => 'GB-KSH'],
            ['nama_barang' => 'Posyandu Tulip Kelurahan Batujaya', 'kode_barang' => '01.01.11.02.01.005', 'nup' => 1, 'luas' => 100.00, 'th_perolehan' => '2014-07-01', 'harga' => 172_000_000, 'umur' => 30, 'sisa' => 18, 'lokasi' => 'Kel. Batujaya', 'kat' => 'GB-KSH'],
            ['nama_barang' => 'Gedung Serbaguna Kelurahan Batujaya', 'kode_barang' => '01.01.11.01.03.001', 'nup' => 1, 'luas' => 250.00, 'th_perolehan' => '2018-01-01', 'harga' => 450_000_000, 'umur' => 30, 'sisa' => 22, 'lokasi' => 'Kel. Batujaya', 'kat' => 'GB-SBG'],
            ['nama_barang' => 'Gedung Serbaguna Kelurahan Kebon Besar', 'kode_barang' => '01.01.11.01.03.002', 'nup' => 1, 'luas' => 280.00, 'th_perolehan' => '2019-06-01', 'harga' => 480_000_000, 'umur' => 30, 'sisa' => 23, 'lokasi' => 'Kel. Kebon Besar', 'kat' => 'GB-SBG'],
            ['nama_barang' => 'Bangunan Jembatan Penghubung Batuceper', 'kode_barang' => '01.01.11.05.02.001', 'nup' => 1, 'luas' => 80.00, 'th_perolehan' => '2006-04-01', 'harga' => 780_000_000, 'umur' => 50, 'sisa' => 30, 'lokasi' => 'Kec. Batuceper', 'kat' => 'GB-INF'],
            ['nama_barang' => 'Gedung Arsip Kecamatan Batuceper', 'kode_barang' => '01.01.11.01.01.003', 'nup' => 1, 'luas' => 80.00, 'th_perolehan' => '2017-03-01', 'harga' => 195_000_000, 'umur' => 30, 'sisa' => 21, 'lokasi' => 'Jl. Raya Batuceper', 'kat' => 'GB-KTR'],
            ['nama_barang' => 'Gudang BMD Kecamatan Batuceper', 'kode_barang' => '01.01.11.01.01.004', 'nup' => 1, 'luas' => 60.00, 'th_perolehan' => '2020-07-01', 'harga' => 120_000_000, 'umur' => 25, 'sisa' => 24, 'lokasi' => 'Jl. Raya Batuceper', 'kat' => 'GB-KTR'],
            ['nama_barang' => 'Gedung Ruang Pelayanan Adminduk Batuceper', 'kode_barang' => '01.01.11.01.01.005', 'nup' => 1, 'luas' => 180.00, 'th_perolehan' => '2011-09-01', 'harga' => 310_000_000, 'umur' => 35, 'sisa' => 16, 'lokasi' => 'Jl. Raya Batuceper No. 2', 'kat' => 'GB-KTR'],
            ['nama_barang' => 'Posyandu Kenanga Kelurahan Batuceper', 'kode_barang' => '01.01.11.02.01.006', 'nup' => 1, 'luas' => 90.00, 'th_perolehan' => '2009-12-01', 'harga' => 148_000_000, 'umur' => 30, 'sisa' => 14, 'lokasi' => 'Kel. Batuceper', 'kat' => 'GB-KSH'],
        ];

        foreach ($asetDummy as $item) {
            $kategori = KategoriAset::where('kode', $item['kat'])->first();

            if (! $kategori) {
                continue;
            }

            $tahunPerolehan = $item['th_perolehan'];
            $harga = $item['harga'];
            $umur = $item['umur'];
            $sisa = $item['sisa'];
            $penyusutanPersen = ($umur - $sisa) / $umur;
            $akumulasiPenyusutan = round($harga * $penyusutanPersen);

            Aset::updateOrCreate(
                ['kode_barang' => $item['kode_barang'], 'nup' => $item['nup']],
                [
                    'kategori_aset_id' => $kategori->id,
                    'nama_barang' => $item['nama_barang'],
                    'jumlah' => 1,
                    'luas' => $item['luas'],
                    'tanggal_perolehan' => $tahunPerolehan,
                    'harga_satuan' => $harga,
                    'nilai_perolehan' => $harga,
                    'umur_ekonomis' => $umur,
                    'akumulasi_penyusutan' => $akumulasiPenyusutan,
                    'sisa_ueb' => $sisa,
                    'nilai_buku' => $harga - $akumulasiPenyusutan,
                    'lokasi' => $item['lokasi'],
                    'status' => StatusAset::Aktif,
                ]
            );
        }
    }
}
