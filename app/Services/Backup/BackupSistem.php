<?php

declare(strict_types=1);

namespace App\Services\Backup;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Cadangan sistem dalam satu berkas ZIP di disk privat `local` (folder backup/):
 * database.sql (dump SQL murni PHP — tanpa mysqldump) + foto aset (disk public) + berkas laporan.
 * Tanpa paket tambahan (ZipArchive bawaan PHP).
 */
final class BackupSistem
{
    public const FOLDER = 'backup';

    private const POLA_NAMA = '/^sikaset-\d{8}-\d{6}\.zip$/';

    /** Jumlah cadangan terbaru yang selalu disimpan walau melewati masa simpan. */
    private const MINIMAL_DISIMPAN = 3;

    /**
     * @return string nama berkas cadangan yang dibuat
     */
    public function buat(): string
    {
        $nama = 'sikaset-'.now()->format('Ymd-His').'.zip';
        $disk = Storage::disk('local');
        $disk->makeDirectory(self::FOLDER);
        $sementara = $disk->path(self::FOLDER.'/.'.$nama.'.tmp');

        $zip = new ZipArchive;
        if ($zip->open($sementara, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Berkas cadangan tidak dapat dibuat. Periksa izin tulis folder storage.');
        }

        $zip->addFromString('database.sql', $this->dumpDatabase());
        $this->tambahFolder($zip, Storage::disk('public')->path(''), 'storage-public');
        $this->tambahFolder($zip, $disk->path('laporan'), 'storage-laporan');
        $zip->addFromString('BACA-SAYA.txt', $this->petunjuk());
        $zip->close();

        rename($sementara, $disk->path(self::FOLDER.'/'.$nama));

        return $nama;
    }

    /**
     * @return list<array{nama: string, ukuran: int, waktu: Carbon}>
     */
    public function daftar(): array
    {
        $disk = Storage::disk('local');

        return collect($disk->files(self::FOLDER))
            ->map(fn (string $path) => basename($path))
            ->filter(fn (string $nama) => preg_match(self::POLA_NAMA, $nama) === 1)
            ->map(fn (string $nama) => [
                'nama' => $nama,
                'ukuran' => (int) $disk->size(self::FOLDER.'/'.$nama),
                'waktu' => Carbon::createFromTimestamp($disk->lastModified(self::FOLDER.'/'.$nama))->setTimezone(config('app.timezone')),
            ])
            ->sortByDesc('nama')->values()->all();
    }

    /**
     * Hapus cadangan yang lebih tua dari $hari hari (selalu menyisakan 3 terbaru).
     *
     * @return int jumlah berkas yang dihapus
     */
    public function bersihkan(int $hari): int
    {
        $batas = now()->subDays($hari);
        $hapus = collect($this->daftar())->slice(self::MINIMAL_DISIMPAN)->filter(fn (array $b) => $b['waktu']->lt($batas));
        $hapus->each(fn (array $b) => Storage::disk('local')->delete(self::FOLDER.'/'.$b['nama']));

        return $hapus->count();
    }

    public function path(string $nama): string
    {
        if (preg_match(self::POLA_NAMA, $nama) !== 1 || ! Storage::disk('local')->exists(self::FOLDER.'/'.$nama)) {
            throw new RuntimeException('Berkas cadangan tidak ditemukan.');
        }

        return self::FOLDER.'/'.$nama;
    }

    public function hapus(string $nama): void
    {
        Storage::disk('local')->delete($this->path($nama));
    }

    private function dumpDatabase(): string
    {
        $mysql = DB::getDriverName() === 'mysql';
        $tabel = Schema::getTableListing($mysql ? DB::getDatabaseName() : null, false);
        $sql = ['-- Cadangan database SIKASET '.now()->toDateTimeString(), $mysql ? 'SET FOREIGN_KEY_CHECKS=0;' : 'PRAGMA foreign_keys=OFF;', ''];

        foreach ($tabel as $nama) {
            $sql[] = 'DROP TABLE IF EXISTS '.$this->kutip($nama, $mysql).';';
            $sql[] = $this->skema($nama, $mysql).';';

            foreach (DB::table($nama)->cursor() as $baris) {
                $nilai = array_map(fn ($v) => $v === null ? 'NULL' : DB::getPdo()->quote((string) $v), (array) $baris);
                $kolom = implode(', ', array_map(fn ($k) => $this->kutip((string) $k, $mysql), array_keys((array) $baris)));
                $sql[] = 'INSERT INTO '.$this->kutip($nama, $mysql)." ({$kolom}) VALUES (".implode(', ', $nilai).');';
            }

            $sql[] = '';
        }

        $sql[] = $mysql ? 'SET FOREIGN_KEY_CHECKS=1;' : 'PRAGMA foreign_keys=ON;';

        return implode("\n", $sql)."\n";
    }

    private function skema(string $tabel, bool $mysql): string
    {
        if ($mysql) {
            return (string) data_get(DB::selectOne('SHOW CREATE TABLE '.$this->kutip($tabel, true)), 'Create Table');
        }

        return (string) DB::table('sqlite_master')->where('type', 'table')->where('name', $tabel)->value('sql');
    }

    private function kutip(string $nama, bool $mysql): string
    {
        return $mysql ? '`'.str_replace('`', '``', $nama).'`' : '"'.str_replace('"', '""', $nama).'"';
    }

    private function tambahFolder(ZipArchive $zip, string $folder, string $awalan): void
    {
        if (! is_dir($folder)) {
            return;
        }

        $berkas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS));

        foreach ($berkas as $item) {
            if ($item instanceof \SplFileInfo && $item->isFile() && $item->getFilename() !== '.gitignore') {
                $relatif = str_replace('\\', '/', substr($item->getPathname(), strlen(rtrim($folder, '\\/')) + 1));
                $zip->addFile($item->getPathname(), $awalan.'/'.$relatif);
            }
        }
    }

    private function petunjuk(): string
    {
        return implode("\n", [
            'CADANGAN SIKASET — cara memulihkan (dilakukan teknisi):',
            '1. Buat database kosong, lalu impor database.sql (mis. mysql -u root nama_db < database.sql).',
            '2. Salin isi folder storage-public/ ke storage/app/public/ aplikasi.',
            '3. Salin isi folder storage-laporan/ ke storage/app/private/laporan/.',
            '4. Pastikan APP_KEY di .env SAMA dengan server lama (token Telegram & sandi SMTP terenkripsi dengan kunci ini).',
            '5. Jalankan: php artisan storage:link && php artisan optimize:clear',
        ])."\n";
    }
}
