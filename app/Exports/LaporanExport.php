<?php

declare(strict_types=1);

namespace App\Exports;

use App\Enums\JenisLaporan;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Workbook laporan per jenis: Peringkat / Keputusan / Lengkap (kriteria + nilai + peringkat + keputusan).
 */
final class LaporanExport implements Export, WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $data  hasil LaporanGenerator::data()
     */
    public function __construct(
        private readonly JenisLaporan $jenis,
        private readonly array $data,
    ) {}

    /**
     * @return list<LaporanSheet>
     */
    public function sheets(): array
    {
        $peringkat = new LaporanSheet('Peringkat', ['Peringkat', 'Kode Barang', 'NUP', 'Nama Barang', 'Kategori', 'Yi', 'Skor Relatif', 'Rekomendasi'],
            $this->data['baris']->map(fn (array $b) => [
                $b['hasil']->ranking, $b['aset']->kode_barang, $b['aset']->nup, $b['aset']->nama_barang, $b['aset']->kategori?->nama,
                round($b['hasil']->yi, 4), round($b['hasil']->skor_relatif, 2), $b['hasil']->rekomendasi->label(),
            ])->all());

        $keputusan = new LaporanSheet('Keputusan', ['Peringkat', 'Kode Barang', 'NUP', 'Nama Barang', 'Rekomendasi Sistem', 'Keputusan Pimpinan', 'Catatan'],
            $this->data['baris']->map(fn (array $b) => [
                $b['hasil']->ranking, $b['aset']->kode_barang, $b['aset']->nup, $b['aset']->nama_barang,
                $b['hasil']->rekomendasi->label(), $b['keputusan']?->tindakan->label() ?? 'Belum diputuskan', $b['keputusan']?->catatan,
            ])->all());

        if ($this->jenis !== JenisLaporan::Lengkap) {
            return [$this->jenis === JenisLaporan::Peringkat ? $peringkat : $keputusan];
        }

        $kriteria = $this->data['kriteria'];

        return [
            new LaporanSheet('Kriteria', ['Kode', 'Nama Kriteria', 'Tipe', 'Bobot (%)'],
                $kriteria->map(fn (array $k) => [$k['kode'], $k['nama'], $k['tipe'] === 'cost' ? 'Cost' : 'Benefit', round($k['bobot'] * 100, 2)])->all()),
            new LaporanSheet('Matriks Nilai', ['Nama Barang', ...$kriteria->pluck('kode')->all()],
                $this->data['hasil']->map(fn ($h) => [$h->aset?->nama_barang, ...$kriteria->map(fn (array $k) => $this->data['matriks'][$h->aset_id][$k['id']] ?? null)->all()])->all()),
            $peringkat,
            $keputusan,
        ];
    }
}
