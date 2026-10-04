<?php

declare(strict_types=1);

namespace App\Actions\Pengguna;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Lupa kata sandi lewat email (password broker Laravel, token 60 menit, tabel password_reset_tokens).
 * Hanya akun aktif yang memiliki email yang dapat menerima tautan.
 */
final class ResetPasswordLewatEmail
{
    /**
     * Kirim tautan reset. Hasil sengaja tidak dibedakan (terdaftar/tidak) agar email tidak bisa ditebak.
     */
    public function kirimTautan(string $email): void
    {
        Password::sendResetLink(['email' => $email, 'is_active' => true]);
    }

    /**
     * @param  array<string, mixed>  $credentials  email, password, password_confirmation, token
     */
    public function atur(array $credentials): void
    {
        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'must_change_password' => false,
                'remember_token' => Str::random(60),
            ])->save();

            activity('pengguna')->performedOn($user)->causedBy($user)->log("Kata sandi diatur ulang lewat email: {$user->username}");
            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Minta tautan baru dari halaman Lupa Kata Sandi.',
            ]);
        }
    }
}
