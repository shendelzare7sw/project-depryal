<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use App\Enums\FormatLaporan;
use App\Enums\JenisLaporan;
use App\Exports\LaporanExport;
use App\Models\Laporan;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Services\Peringkat\PeringkatData;
use App\Support\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Membuat berkas laporan (PDF A4 / Excel) untuk satu periode yang sudah dihitung, menyimpannya di disk
 * privat `local` (folder laporan/) dan mencatat riwayat cetak di tabel `laporan`.
 */
final class LaporanGenerator
{
    private const DISK = 'local';

    public function __construct(private readonly PeringkatData $peringkat) {}

    public function generate(JenisLaporan $jenis, FormatLaporan $format, PeriodePenilaian $periode, User $by): Laporan
    {
        $data = $this->data($jenis, $periode, $by);
        $namaFile = $jenis->value.'-'.Str::slug($periode->nama).'-'.now()->format('Ymd-His').'.'.$format->value;
        $path = 'laporan/'.Str::uuid().'.'.$format->value;

        $isi = $format === FormatLaporan::Pdf
            ? Pdf::loadView('laporan.pdf.dokumen', $data)->setPaper('a4', 'portrait')->output()
            : Excel::raw(new LaporanExport($jenis, $data), ExcelWriter::XLSX);

        Storage::disk(self::DISK)->put($path, $isi);

        return Laporan::create([
            'user_id' => $by->id,
            'periode_id' => $periode->id,
            'jenis' => $jenis,
            'format' => $format,
            'nama_file' => $namaFile,
            'path' => $path,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function data(JenisLaporan $jenis, PeriodePenilaian $periode, User $by): array
    {
        $peringkat = $this->peringkat->index($periode);

        if ($peringkat['total'] === 0) {
            throw new DomainException('Periode ini belum memiliki hasil MOORA. Laporan dapat dibuat setelah perhitungan.');
        }

        return $peringkat + $this->peringkat->perhitungan($periode) + [
            'jenis' => $jenis,
            'dicetakOleh' => $by,
            'instansi' => [
                'nama' => Setting::namaInstansi(),
                'alamat' => Setting::alamatInstansi(),
                'penandatangan' => Setting::namaPenandatangan(),
                'nip' => Setting::nipPenandatangan(),
                'jabatan' => Setting::jabatanPenandatangan(),
            ],
            'logo' => public_path('img/logo.jpeg'),
        ];
    }

    public function path(Laporan $laporan): string
    {
        if (! Storage::disk(self::DISK)->exists($laporan->path)) {
            throw new DomainException('Berkas laporan tidak ditemukan. Silakan buat ulang laporan.');
        }

        return $laporan->path;
    }
}
