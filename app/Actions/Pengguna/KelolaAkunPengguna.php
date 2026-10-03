<?php

declare(strict_types=1);

namespace App\Actions\Pengguna;

use App\Models\User;
use DomainException;
use Illuminate\Support\Str;

/**
 * Aksi admin terhadap akun lain: aktif/nonaktif dan reset password (password acak ditampilkan sekali).
 */
final class KelolaAkunPengguna
{
    public function ubahStatus(User $target, User $admin): User
    {
        $this->tolakDiriSendiri($target, $admin, 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        $target->update(['is_active' => ! $target->is_active]);

        return $target;
    }

    /**
     * @return string password baru (plain) untuk ditunjukkan sekali ke admin
     */
    public function resetPassword(User $target, User $admin): string
    {
        $this->tolakDiriSendiri($target, $admin, 'Gunakan menu Profil untuk mengganti password Anda sendiri.');
        $password = Str::password(10, symbols: false);
        $target->update(['password' => $password]);
        activity('pengguna')->performedOn($target)->causedBy($admin)->log("Password pengguna direset: {$target->username}");

        return $password;
    }

    private function tolakDiriSendiri(User $target, User $admin, string $pesan): void
    {
        if ($target->is($admin)) {
            throw new DomainException($pesan);
        }
    }
}
