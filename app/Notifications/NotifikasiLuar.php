<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Support\Integrasi;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Salinan SistemNotification untuk kanal di luar aplikasi (diatur Admin di menu Integrasi):
 * email (notifikasi email aktif & pengguna punya email) dan Telegram (bot aktif & akun terhubung).
 */
class NotifikasiLuar extends Notification
{
    public function __construct(public readonly SistemNotification $asal) {}

    public static function adaKanalAktif(): bool
    {
        return Integrasi::emailNotifikasiAktif() || Integrasi::telegramAktif();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $kanal = [];

        if (Integrasi::emailNotifikasiAktif() && filled(data_get($notifiable, 'email'))) {
            $kanal[] = 'mail';
        }

        if (Integrasi::telegramAktif() && filled(data_get($notifiable, 'telegram_chat_id'))) {
            $kanal[] = TelegramChannel::class;
        }

        return $kanal;
    }

    public function toMail(User $notifiable): MailMessage
    {
        $pesan = (new MailMessage)
            ->subject('SIKASET: '.$this->asal->judul)
            ->greeting('Halo, '.$notifiable->name)
            ->line($this->asal->pesan);

        if ($this->asal->url) {
            $pesan->action('Buka di SIKASET', $this->asal->url);
        }

        return $pesan->salutation('Salam, SIKASET Kecamatan Batuceper');
    }

    public function toTelegram(): string
    {
        $teks = '<b>SIKASET — '.e($this->asal->judul)."</b>\n".e($this->asal->pesan);

        return $this->asal->url ? $teks."\n\n".e($this->asal->url) : $teks;
    }
}
