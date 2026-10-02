<?php

declare(strict_types=1);

namespace App\Services\Aset;

use App\Imports\AsetImport;
use App\Imports\AsetImportResult;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;

/**
 * Berkas Excel BMD sementara (satu per pengguna) di disk privat `local`,
 * dipakai antara langkah unggah → pratinjau → konfirmasi simpan.
 */
final class AsetImportFile
{
    private const DISK = 'local';

    private const EXTENSIONS = ['xlsx', 'xls'];

    public function put(User $user, UploadedFile $file): void
    {
        $this->clear($user);

        $file->storeAs('imports', "aset-{$user->id}.".strtolower($file->getClientOriginalExtension()), self::DISK);
    }

    public function read(User $user): AsetImportResult
    {
        $path = $this->path($user)
            ?? throw new DomainException('Belum ada berkas import. Silakan unggah berkas Excel BMD terlebih dahulu.');

        $import = new AsetImport;

        try {
            Excel::import($import, $path, self::DISK);
        } catch (SpreadsheetException) {
            throw new DomainException('Berkas tidak dapat dibaca. Pastikan berkas berformat Excel (.xlsx/.xls) yang valid.');
        }

        return $import->result();
    }

    public function clear(User $user): void
    {
        foreach (self::EXTENSIONS as $ext) {
            Storage::disk(self::DISK)->delete("imports/aset-{$user->id}.{$ext}");
        }
    }

    private function path(User $user): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            if (Storage::disk(self::DISK)->exists($path = "imports/aset-{$user->id}.{$ext}")) {
                return $path;
            }
        }

        return null;
    }
}
