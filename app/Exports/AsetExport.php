<?php

declare(strict_types=1);

namespace App\Exports;

use App\Imports\AsetImport;
use App\Models\Aset;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Ekspor seluruh daftar aset. Kolom = template import (+ status) agar berkas hasil ekspor bisa diimpor ulang.
 */
final class AsetExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * @return Builder<Aset>
     */
    public function query(): Builder
    {
        return Aset::query()->with('kategori')->orderBy('kode_barang')->orderBy('nup');
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [...AsetImport::HEADINGS, 'Status'];
    }

    /**
     * @param  Aset  $row
     * @return list<mixed>
     */
    public function map(mixed $row): array
    {
        return [
            $row->kategori?->nama,
            $row->kode_barang,
            $row->nup,
            $row->nama_barang,
            $row->jumlah,
            $row->luas !== null ? (float) $row->luas : null,
            $row->tanggal_perolehan?->format('d/m/Y'),
            (float) $row->harga_satuan,
            (float) $row->nilai_perolehan,
            $row->umur_ekonomis,
            (float) $row->akumulasi_penyusutan,
            $row->sisa_ueb,
            (float) $row->nilai_buku,
            $row->lokasi,
            $row->status->label(),
        ];
    }
}
