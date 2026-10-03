<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use App\Models\User;
use App\Support\Setting;
use Spatie\Activitylog\Models\Activity;

/**
 * Dashboard Administrator: statistik sistem, pengguna per role, ambang, aktivitas terbaru.
 */
final class AdminDashboard
{
    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $perRole = User::query()->selectRaw('role, COUNT(*) as jumlah, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as aktif')
            ->groupBy('role')->get()->keyBy(fn ($r) => (string) $r->getRawOriginal('role'));

        return [
            'stats' => [
                ['label' => 'Pengguna aktif', 'value' => User::where('is_active', true)->count(), 'icon' => 'users', 'tone' => 'violet', 'meta' => User::where('is_active', false)->count().' nonaktif', 'href' => route('pengguna.index')],
                ['label' => 'Total aset BMD', 'value' => Aset::count(), 'icon' => 'building-office-2', 'tone' => 'brand', 'href' => route('aset.index')],
                ['label' => 'Periode final', 'value' => PeriodePenilaian::where('status', StatusPeriode::Final->value)->count(), 'icon' => 'lock-closed', 'tone' => 'emerald', 'meta' => PeriodePenilaian::aktif()->count().' periode aktif'],
                ['label' => 'Aktivitas 7 hari', 'value' => Activity::where('created_at', '>=', now()->subDays(7))->count(), 'icon' => 'clipboard-document-list', 'tone' => 'sky', 'meta' => 'Tercatat di audit log'],
            ],
            'roles' => collect(UserRole::cases())->map(fn (UserRole $r) => [
                'role' => $r,
                'jumlah' => (int) ($perRole[$r->value]->jumlah ?? 0),
                'aktif' => (int) ($perRole[$r->value]->aktif ?? 0),
            ])->all(),
            'ambang' => ['pertahankan' => Setting::ambangPertahankan(), 'perbaiki' => Setting::ambangPerbaiki()],
            'aktivitas' => Activity::with('causer')->latest('id')->limit(6)->get(),
            'periode' => PeriodePenilaian::withCount('aset')->latest('id')->limit(4)->get(),
        ];
    }
}
