<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\SistemNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Pengirim notifikasi dalam aplikasi ke pengguna aktif per role.
 */
final class Notifikasi
{
    /**
     * @param  list<UserRole>  $roles
     */
    public function kirimKeRole(array $roles, SistemNotification $notifikasi, ?User $kecuali = null): void
    {
        $penerima = User::query()
            ->where('is_active', true)
            ->whereIn('role', array_map(fn (UserRole $r) => $r->value, $roles))
            ->when($kecuali, fn ($q) => $q->whereKeyNot($kecuali?->id))
            ->get();

        Notification::send($penerima, $notifikasi);
    }
}
