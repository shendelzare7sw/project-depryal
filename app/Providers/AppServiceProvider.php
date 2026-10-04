<?php

namespace App\Providers;

use App\Support\Integrasi;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Endpoint berat (import/export Excel, hitung MOORA, buat laporan, reset password): 20 permintaan/menit per pengguna.
        RateLimiter::for('berat', fn (Request $request) => Limit::perMinute(20)
            ->by((string) ($request->user()->id ?? $request->ip()))
            ->response(fn () => back()->with('error', 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.')));

        // Halaman tamu yang mengirim email (lupa kata sandi): 5 permintaan/menit per IP.
        RateLimiter::for('tamu', fn (Request $request) => Limit::perMinute(5)->by((string) $request->ip())
            ->response(fn () => back()->withErrors(['email' => 'Terlalu banyak permintaan. Tunggu satu menit lalu coba lagi.'])));

        // SMTP dari menu Integrasi (database) menimpa konfigurasi bawaan.
        Integrasi::terapkan();

        ResetPassword::toMailUsing(fn (object $notifiable, string $token) => (new MailMessage)
            ->subject('Atur Ulang Kata Sandi SIKASET')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun SIKASET Anda.')
            ->action('Buat Kata Sandi Baru', route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line('Tautan ini berlaku 60 menit.')
            ->line('Bila Anda tidak meminta reset, abaikan email ini — kata sandi Anda tidak berubah.')
            ->salutation('Salam, SIKASET Kecamatan Batuceper'));
    }
}
