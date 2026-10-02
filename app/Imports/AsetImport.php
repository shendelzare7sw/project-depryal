<?php

declare(strict_types=1);

namespace App\Imports;

use DateTime;
use DomainException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Pembaca Excel data BMD (11 kolom BMD + kategori + NUP opsional).
 * Hanya sheet pertama yang dibaca; baris judul dicari otomatis pada 10 baris teratas.
 * Angka format Indonesia (1.234.567,89) dan tanggal dd/mm/yyyy didukung.
 */
final class AsetImport implements ToArray, WithCalculatedFormulas
{
    /**
     * Judul kolom template (urutan = urutan kolom di Excel).
     */
    public const HEADINGS = [
        'Kategori', 'Kode Barang', 'NUP', 'Nama Barang', 'Jumlah', 'Luas (m2)', 'Tanggal Perolehan',
        'Harga Satuan', 'Nilai Perolehan', 'Umur Ekonomis (UEB)', 'Akumulasi Penyusutan', 'Sisa UEB',
        'Nilai Buku', 'Lokasi',
    ];

    /**
     * field => daftar slug judul kolom yang diterima.
     */
    private const ALIASES = [
        'kategori' => ['kategori', 'kategori_aset', 'jenis_aset'],
        'kode_barang' => ['kode_barang', 'kode'],
        'nup' => ['nup', 'no_register', 'register', 'nomor_urut_pendaftaran'],
        'nama_barang' => ['nama_barang', 'nama_aset', 'nama'],
        'jumlah' => ['jumlah', 'jml'],
        'luas' => ['luas_m2', 'luas', 'luas_m'],
        'tanggal_perolehan' => ['tanggal_perolehan', 'tgl_perolehan', 'tahun_perolehan', 'thn_perolehan'],
        'harga_satuan' => ['harga_satuan', 'harga'],
        'nilai_perolehan' => ['nilai_perolehan'],
        'umur_ekonomis' => ['umur_ekonomis_ueb', 'umur_ekonomis', 'ueb', 'masa_manfaat'],
        'akumulasi_penyusutan' => ['akumulasi_penyusutan', 'akum_penyusutan', 'penyusutan'],
        'sisa_ueb' => ['sisa_ueb', 'sisa_umur_ekonomis', 'sisa_masa_manfaat'],
        'nilai_buku' => ['nilai_buku'],
        'lokasi' => ['lokasi', 'alamat'],
    ];

    private const NUMBERS = ['luas', 'harga_satuan', 'nilai_perolehan', 'akumulasi_penyusutan', 'nilai_buku'];

    private const INTEGERS = ['nup', 'jumlah', 'umur_ekonomis', 'sisa_ueb'];

    /** @var array<int, array<int, mixed>>|null */
    private ?array $sheet = null;

    /**
     * @param  array<int, array<int, mixed>>  $array
     */
    public function array(array $array): void
    {
        $this->sheet ??= $array;
    }

    public function result(): AsetImportResult
    {
        $rows = $this->sheet ?? [];
        $headerIndex = $this->findHeaderRow($rows);

        if ($headerIndex === null) {
            throw new DomainException('Judul kolom "Kode Barang" dan "Nama Barang" tidak ditemukan. Gunakan template import BMD.');
        }

        $columns = $this->mapColumns($rows[$headerIndex]);
        $parsed = [];

        foreach (array_slice($rows, $headerIndex + 1, null, true) as $index => $row) {
            $raw = array_map(fn (int $col) => $row[$col] ?? null, $columns);

            if (collect($raw)->every(fn ($v) => $v === null || trim((string) $v) === '')) {
                continue;
            }

            $parsed[] = ['baris' => $index + 1, 'data' => $this->normalize($raw)];
        }

        return $this->validate($this->assignNup($parsed));
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function findHeaderRow(array $rows): ?int
    {
        foreach (array_slice($rows, 0, 10, true) as $index => $row) {
            $columns = $this->mapColumns($row);

            if (isset($columns['kode_barang'], $columns['nama_barang'])) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $header
     * @return array<string, int> field => indeks kolom
     */
    private function mapColumns(array $header): array
    {
        $slugs = array_map(fn ($h) => Str::slug((string) $h, '_'), $header);
        $columns = [];

        foreach (self::ALIASES as $field => $aliases) {
            foreach ($aliases as $alias) {
                $col = array_search($alias, $slugs, true);

                if ($col !== false && ! in_array($col, $columns, true)) {
                    $columns[$field] = (int) $col;
                    break;
                }
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normalize(array $raw): array
    {
        $data = [];

        foreach (array_keys(self::ALIASES) as $field) {
            $value = $raw[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $value = $value === '' ? null : $value;

            $data[$field] = match (true) {
                in_array($field, self::NUMBERS, true) => $this->number($value),
                in_array($field, self::INTEGERS, true) => $this->integer($value),
                $field === 'tanggal_perolehan' => $this->date($value),
                default => $value === null ? null : (string) $value,
            };
        }

        return $this->applyDefaults($data);
    }

    /**
     * Kolom opsional diisi dari kolom lain bila kosong.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyDefaults(array $data): array
    {
        $data['jumlah'] ??= 1;
        $data['akumulasi_penyusutan'] ??= 0;

        if (is_numeric($data['nilai_perolehan']) && is_numeric($data['jumlah']) && $data['jumlah'] > 0) {
            $data['harga_satuan'] ??= round($data['nilai_perolehan'] / $data['jumlah'], 2);
        }

        if (is_numeric($data['nilai_perolehan']) && is_numeric($data['akumulasi_penyusutan'])) {
            $data['nilai_buku'] ??= max(0, $data['nilai_perolehan'] - $data['akumulasi_penyusutan']);
        }

        return $data;
    }

    private function number(mixed $value): mixed
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }

        $clean = (string) preg_replace('/[^\d,.\-]/', '', (string) $value);

        if (str_contains($clean, ',')) {
            $clean = str_replace(['.', ','], ['', '.'], $clean);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $clean)) {
            $clean = str_replace('.', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : $value;
    }

    private function integer(mixed $value): mixed
    {
        $number = $this->number($value);

        return is_float($number) && floor($number) === $number ? (int) $number : $number;
    }

    /**
     * Terima serial tanggal Excel, tahun saja (2005 → 01/01/2005), dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd.
     */
    private function date(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            return $number >= 1900 && $number <= 2100 && floor($number) === $number
                ? sprintf('%04d-01-01', $number)
                : ExcelDate::excelToDateTimeObject($number)->format('Y-m-d');
        }

        $text = trim((string) $value);

        foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'd.m.Y', 'Y-m-d'] as $format) {
            $date = DateTime::createFromFormat('!'.$format, $text);

            if ($date !== false && $date->format($format) === $text) {
                return $date->format('Y-m-d');
            }
        }

        return $text;
    }

    /**
     * NUP kosong diisi berurutan per kode barang (melewati NUP yang sudah tertulis di berkas).
     *
     * @param  list<array{baris: int, data: array<string, mixed>}>  $rows
     * @return list<array{baris: int, data: array<string, mixed>}>
     */
    private function assignNup(array $rows): array
    {
        $used = [];

        foreach ($rows as $row) {
            if (is_int($row['data']['nup'])) {
                $used[(string) $row['data']['kode_barang']][] = $row['data']['nup'];
            }
        }

        foreach ($rows as $i => $row) {
            if ($row['data']['nup'] !== null) {
                continue;
            }

            $kode = (string) $row['data']['kode_barang'];
            $nup = 1;

            while (in_array($nup, $used[$kode] ?? [], true)) {
                $nup++;
            }

            $used[$kode][] = $nup;
            $rows[$i]['data']['nup'] = $nup;
        }

        return $rows;
    }

    /**
     * @param  list<array{baris: int, data: array<string, mixed>}>  $rows
     */
    private function validate(array $rows): AsetImportResult
    {
        $valid = [];
        $invalid = [];
        $seen = [];

        foreach ($rows as $row) {
            $errors = Validator::make($row['data'], $this->rules(), [
                'tanggal_perolehan.date_format' => 'Tanggal perolehan tidak valid (gunakan format dd/mm/yyyy).',
                'tanggal_perolehan.before_or_equal' => 'Tanggal perolehan tidak boleh melewati hari ini.',
            ])->errors()->all();
            $key = $row['data']['kode_barang'].'#'.$row['data']['nup'];

            if (isset($seen[$key])) {
                $errors[] = "Kode barang + NUP sama dengan baris {$seen[$key]}.";
            }

            $seen[$key] ??= $row['baris'];

            if ($errors === []) {
                $valid[] = $row;
            } else {
                $invalid[] = $row + ['errors' => $errors];
            }
        }

        return new AsetImportResult($valid, $invalid);
    }

    /**
     * @return array<string, list<string>>
     */
    private function rules(): array
    {
        return [
            'kategori' => ['required', 'string', 'max:100'],
            'kode_barang' => ['required', 'string', 'max:50'],
            'nup' => ['required', 'integer', 'min:1'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'luas' => ['nullable', 'numeric', 'min:0'],
            'tanggal_perolehan' => ['bail', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
            'nilai_perolehan' => ['required', 'numeric', 'min:0'],
            'umur_ekonomis' => ['required', 'integer', 'min:0'],
            'akumulasi_penyusutan' => ['required', 'numeric', 'min:0'],
            'sisa_ueb' => ['required', 'integer', 'min:0', 'lte:umur_ekonomis'],
            'nilai_buku' => ['required', 'numeric', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ];
    }
}
