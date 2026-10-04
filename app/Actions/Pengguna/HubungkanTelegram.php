<?php

declare(strict_types=1);

namespace App\Actions\Pengguna;

use App\Models\User;
use App\Services\Telegram;
use App\Support\Integrasi;
use DomainException;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Menautkan akun pengguna dengan chat Telegram tanpa mengetik chat ID:
 * 1) buat kode sekali pakai → tautan t.me/<bot>?start=<kode>; 2) pengguna menekan Start di Telegram;
 * 3) sistem mencari pesan "/start <kode>" lewat getUpdates lalu menyimpan chat ID.
 */
final class HubungkanTelegram
{
    public function __construct(private readonly Telegram $telegram) {}

    /**
     * @return array{kode: string, tautan: string}
     */
    public function mulai(): array
    {
        if (! Integrasi::telegramAktif()) {
            throw new DomainException('Notifikasi Telegram belum diaktifkan Administrator.');
        }

        $kode = Str::random(24);

        return ['kode' => $kode, 'tautan' => 'https://t.me/'.Integrasi::telegramBot().'?start='.$kode];
    }

    public function selesaikan(User $user, ?string $kode): void
    {
        if (blank($kode) || ! Integrasi::telegramAktif()) {
            throw new DomainException('Sesi penautan berakhir. Tekan "Hubungkan Telegram" lagi.');
        }

        try {
            $chatId = $this->telegram->cariChatId((string) $kode);
        } catch (RuntimeException $e) {
            throw new DomainException($e->getMessage());
        }

        if ($chatId === null) {
            throw new DomainException('Pesan Start belum diterima. Buka bot di Telegram, tekan "Start", lalu coba lagi.');
        }

        $user->update(['telegram_chat_id' => $chatId]);
        $this->telegram->kirim($chatId, '<b>SIKASET</b>: akun '.e($user->name).' terhubung. Notifikasi penting akan dikirim ke sini.');
    }
}
