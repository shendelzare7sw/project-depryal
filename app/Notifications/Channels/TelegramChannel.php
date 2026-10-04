<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Notifications\NotifikasiLuar;
use App\Services\Telegram;
use App\Support\Integrasi;

/**
 * Kanal notifikasi Telegram: aktif bila bot diatur di menu Integrasi dan pengguna sudah menghubungkan akunnya.
 */
class TelegramChannel
{
    public function __construct(private readonly Telegram $telegram) {}

    public function send(object $notifiable, NotifikasiLuar $notification): void
    {
        $chatId = (string) data_get($notifiable, 'telegram_chat_id');

        if (Integrasi::telegramAktif() && $chatId !== '') {
            $this->telegram->kirim($chatId, $notification->toTelegram());
        }
    }
}
