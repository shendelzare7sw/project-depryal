<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Backup\BackupSistem;
use App\Support\Setting;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Menu Cadangan (Admin): daftar, buat sekarang, unduh, hapus. Cadangan harian dibuat scheduler (sikaset:backup).
 */
class BackupController extends Controller
{
    public function index(BackupSistem $backup): View
    {
        return view('backup.index', ['cadangan' => $backup->daftar(), 'simpanHari' => Setting::backupSimpanHari()]);
    }

    public function store(BackupSistem $backup): RedirectResponse
    {
        $nama = $this->coba(fn () => $backup->buat());

        return to_route('backup.index')->with('success', "Cadangan {$nama} berhasil dibuat.");
    }

    public function download(string $nama, BackupSistem $backup): StreamedResponse
    {
        return Storage::disk('local')->download($this->coba(fn () => $backup->path($nama)), $nama);
    }

    public function destroy(string $nama, BackupSistem $backup): RedirectResponse
    {
        $this->coba(fn () => $backup->hapus($nama));

        return to_route('backup.index')->with('success', "Cadangan {$nama} dihapus.");
    }

    /**
     * @template T
     *
     * @param  callable(): T  $aksi
     * @return T
     */
    private function coba(callable $aksi): mixed
    {
        try {
            return $aksi();
        } catch (RuntimeException $e) {
            throw new DomainException($e->getMessage());
        }
    }
}
