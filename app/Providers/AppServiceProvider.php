<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
    }
}
