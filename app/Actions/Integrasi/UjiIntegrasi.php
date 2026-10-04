<?php

declare(strict_types=1);

namespace App\Actions\Integrasi;

use App\Models\User;
use App\Services\Telegram;
use App\Support\Integrasi;
use DomainException;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Kirim pesan uji ke akun Admin sendiri untuk memastikan Telegram/SMTP bekerja.
 */
final class UjiIntegrasi
{
    public function __construct(private readonly Telegram $telegram) {}

    /**
     * @return string pesan hasil untuk ditampilkan
     */
    public function execute(string $kanal, User $admin): string
    {
        return match ($kanal) {
            'telegram' => $this->telegram($admin),
            'email' => $this->email($admin),
            default => throw new DomainException('Kanal uji tidak dikenal.'),
        };
    }

    private function telegram(User $admin): string
    {
        if (! Integrasi::telegramAktif() || blank($admin->telegram_chat_id)) {
            throw new DomainException('Simpan token bot lalu hubungkan Telegram Anda di menu Profil terlebih dahulu.');
        }

        $this->coba(fn () => $this->telegram->kirim((string) $admin->telegram_chat_id, '<b>SIKASET</b>: uji notifikasi Telegram berhasil ✅'));

        return 'Pesan uji terkirim ke Telegram Anda.';
    }

    private function email(User $admin): string
    {
        if (! Integrasi::smtpAktif() || blank($admin->email)) {
            throw new DomainException('Simpan pengaturan SMTP dan isi email Anda di menu Profil terlebih dahulu.');
        }

        $this->coba(fn () => Mail::raw('SIKASET: uji pengiriman email berhasil.', fn ($m) => $m->to((string) $admin->email)->subject('Uji email SIKASET')));

        return "Email uji terkirim ke {$admin->email}. Periksa kotak masuk/spam.";
    }

    private function coba(callable $kirim): void
    {
        try {
            $kirim();
        } catch (Throwable $e) {
            throw new DomainException('Pengiriman gagal: '.mb_substr($e->getMessage(), 0, 200));
        }
    }
}
