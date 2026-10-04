<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\NotifikasiLuar;
use App\Notifications\SistemNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Pengirim notifikasi ke pengguna aktif per role: selalu di dalam aplikasi (lonceng), lalu — bila dikonfigurasi —
 * email & WhatsApp setelah respons terkirim. Kegagalan kanal luar hanya dicatat di log, tidak menggagalkan proses.
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

        if (NotifikasiLuar::adaKanalAktif()) {
            defer(fn () => $this->kirimLuar($penerima, $notifikasi));
        }
    }

    /**
     * @param  Collection<int, User>  $penerima
     */
    public function kirimLuar(Collection $penerima, SistemNotification $notifikasi): void
    {
        foreach ($penerima as $user) {
            try {
                $user->notify(new NotifikasiLuar($notifikasi));
            } catch (Throwable $e) {
                Log::warning("Notifikasi luar gagal untuk {$user->username}: {$e->getMessage()}");
            }
        }
    }
}
