<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\TurnstileValid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login dengan username ATAU email; maks 5 percobaan gagal per (login, IP); akun nonaktif ditolak.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];

        // Turnstile aktif hanya bila kunci rahasia dikonfigurasi (.env TURNSTILE_SECRET_KEY).
        if (filled(config('services.turnstile.secret_key'))) {
            $rules['cf-turnstile-response'] = ['required', 'string', new TurnstileValid($this->ip())];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['cf-turnstile-response.required' => 'Selesaikan verifikasi keamanan (centang kotak "Saya bukan robot") terlebih dahulu.'];
    }

    public function authenticate(): void
    {
        $key = Str::transliterate(Str::lower((string) $this->input('login')).'|'.$this->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $field = filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $this->input('login'), 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages(['login' => 'Username/email atau kata sandi salah.']);
        }

        $user = Auth::user();

        if ($user instanceof User && ! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages(['login' => 'Akun Anda dinonaktifkan. Hubungi administrator.']);
        }

        RateLimiter::clear($key);
    }
}
