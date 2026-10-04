<?php

declare(strict_types=1);

namespace App\Actions\Integrasi;

use App\Actions\Pengaturan\SimpanPengaturan;
use App\Models\User;
use App\Services\Telegram;
use App\Support\Integrasi;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

final class SimpanIntegrasi
{
    public function __construct(
        private readonly SimpanPengaturan $pengaturan,
        private readonly Telegram $telegram,
    ) {}

    /**
     * Simpan pengaturan Telegram & SMTP. Kolom rahasia yang dikosongkan tidak mengubah nilai tersimpan;
     * centang hapus_<kunci> untuk menghapusnya. Token bot baru diperiksa ke Telegram (getMe) sebelum disimpan.
     *
     * @param  array<string, mixed>  $data  hasil validated() UpdateIntegrasiRequest
     */
    public function execute(array $data, User $by): void
    {
        $simpan = Arr::only($data, Integrasi::BIASA);
        $simpan['notifikasi_email'] = ($data['notifikasi_email'] ?? false) ? '1' : '0';

        foreach (Integrasi::RAHASIA as $key) {
            if ($data['hapus_'.$key] ?? false) {
                $simpan[$key] = null;
            } elseif (filled($data[$key] ?? null)) {
                $simpan[$key] = Crypt::encryptString((string) $data[$key]);
            }
        }

        if (array_key_exists('telegram_bot_token', $simpan)) {
            $simpan['telegram_bot_username'] = $simpan['telegram_bot_token'] === null ? null : $this->namaBot((string) $data['telegram_bot_token']);
        }

        $this->pengaturan->execute($simpan, $by, Integrasi::RAHASIA);
    }

    private function namaBot(string $token): string
    {
        try {
            return $this->telegram->namaBot($token);
        } catch (RuntimeException $e) {
            throw new DomainException('Token bot Telegram tidak dapat diverifikasi. '.$e->getMessage());
        }
    }
}
