<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Pengaturan integrasi layanan luar (Telegram, SMTP email) yang diisi Admin lewat menu Integrasi
 * dan disimpan di tabel `pengaturan` — bukan di `.env`. Kunci rahasia disimpan terenkripsi (APP_KEY).
 * Nilai yang terisi menimpa konfigurasi bawaan saat aplikasi berjalan (lihat terapkan()).
 */
final class Integrasi
{
    /** @var list<string> */
    public const RAHASIA = ['telegram_bot_token', 'smtp_password'];

    /** @var list<string> */
    public const BIASA = [
        'notifikasi_email', 'smtp_host', 'smtp_port', 'smtp_enkripsi', 'smtp_username', 'smtp_dari_alamat', 'smtp_dari_nama',
    ];

    public static function rahasia(string $key): ?string
    {
        $nilai = Setting::get($key);

        if (blank($nilai)) {
            return null;
        }

        try {
            return Crypt::decryptString((string) $nilai);
        } catch (Throwable) {
            return null;
        }
    }

    public static function terisi(string $key): bool
    {
        return in_array($key, self::RAHASIA, true) ? self::rahasia($key) !== null : filled(Setting::get($key));
    }

    public static function telegramToken(): ?string
    {
        return self::rahasia('telegram_bot_token');
    }

    public static function telegramBot(): ?string
    {
        $bot = Setting::get('telegram_bot_username');

        return blank($bot) ? null : (string) $bot;
    }

    public static function telegramAktif(): bool
    {
        return self::telegramToken() !== null && self::telegramBot() !== null;
    }

    public static function smtpAktif(): bool
    {
        return filled(Setting::get('smtp_host')) && filled(Setting::get('smtp_dari_alamat'));
    }

    public static function emailNotifikasiAktif(): bool
    {
        return Setting::get('notifikasi_email') === '1' && self::smtpAktif();
    }

    /**
     * Terapkan SMTP dari database ke konfigurasi runtime (dipanggil di AppServiceProvider::boot).
     * Kunci Cloudflare Turnstile sengaja tetap di .env: salah isi lewat menu dapat mengunci halaman login.
     */
    public static function terapkan(): void
    {
        if (self::smtpAktif()) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.scheme' => Setting::get('smtp_enkripsi') === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.host' => Setting::get('smtp_host'),
                'mail.mailers.smtp.port' => (int) Setting::get('smtp_port', 587),
                'mail.mailers.smtp.username' => Setting::get('smtp_username'),
                'mail.mailers.smtp.password' => self::rahasia('smtp_password'),
                'mail.from.address' => Setting::get('smtp_dari_alamat'),
                'mail.from.name' => Setting::get('smtp_dari_nama') ?: 'SIKASET',
            ]);
        }
    }
}
