<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Notifications\SistemNotification;
use App\Services\Backup\BackupSistem;
use App\Services\Notifikasi;
use App\Support\Setting;
use Illuminate\Console\Command;
use Throwable;

class BackupSistemCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sikaset:backup';

    /**
     * @var string
     */
    protected $description = 'Buat cadangan database + foto aset + berkas laporan, lalu hapus cadangan lama';

    public function handle(BackupSistem $backup, Notifikasi $notifikasi): int
    {
        try {
            $nama = $backup->buat();
            $dihapus = $backup->bersihkan(Setting::backupSimpanHari());
            $this->info("Cadangan dibuat: {$nama} ({$dihapus} cadangan lama dihapus).");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Cadangan gagal: '.$e->getMessage());
            $notifikasi->kirimKeRole([UserRole::Admin], new SistemNotification(
                judul: 'Cadangan otomatis gagal',
                pesan: mb_substr($e->getMessage(), 0, 200),
                url: route('backup.index'),
                icon: 'exclamation-triangle',
                tone: 'error',
            ));

            return self::FAILURE;
        }
    }
}
