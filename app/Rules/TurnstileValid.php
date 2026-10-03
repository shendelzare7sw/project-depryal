<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifikasi token Cloudflare Turnstile ke server Cloudflare (siteverify).
 */
class TurnstileValid implements ValidationRule
{
    private const URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(private readonly ?string $ip = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $hasil = Http::asForm()->timeout(10)->post(self::URL, [
                'secret' => config('services.turnstile.secret_key'),
                'response' => (string) $value,
                'remoteip' => $this->ip,
            ])->json();
        } catch (Throwable) {
            $hasil = null;
        }

        if (! is_array($hasil) || ($hasil['success'] ?? false) !== true) {
            $fail('Verifikasi keamanan gagal atau kedaluwarsa. Centang ulang kotak verifikasi lalu coba lagi.');
        }
    }
}
