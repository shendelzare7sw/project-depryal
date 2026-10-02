<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi dalam aplikasi (channel database) untuk kejadian penting lintas role.
 * Ditampilkan di lonceng topbar & halaman Notifikasi.
 */
class SistemNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $tone  success|warning|error|info|primary
     */
    public function __construct(
        public readonly string $judul,
        public readonly string $pesan,
        public readonly ?string $url = null,
        public readonly string $icon = 'bell',
        public readonly string $tone = 'primary',
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'judul' => $this->judul,
            'pesan' => $this->pesan,
            'url' => $this->url,
            'icon' => $this->icon,
            'tone' => $this->tone,
        ];
    }
}
