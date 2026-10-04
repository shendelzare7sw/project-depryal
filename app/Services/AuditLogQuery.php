<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ModulAudit;
use App\Support\Tampil;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * Pencarian audit log: filter pengguna (causer), modul (log_name), rentang tanggal.
 */
final class AuditLogQuery
{
    /**
     * @param  array<string, mixed>  $filter  user, modul, dari, sampai
     * @return LengthAwarePaginator<int, Activity>
     */
    public function cari(array $filter): LengthAwarePaginator
    {
        $modul = ModulAudit::tryFrom((string) ($filter['modul'] ?? ''));
        $dari = $this->tanggal($filter['dari'] ?? null);
        $sampai = $this->tanggal($filter['sampai'] ?? null);

        return Tampil::ambil(Activity::query()->with('causer')
            ->when(filled($filter['user'] ?? null), fn ($q) => $q->where('causer_id', (int) $filter['user']))
            ->when($modul, fn ($q) => $q->where('log_name', $modul?->value))
            ->when($dari, fn ($q) => $q->where('created_at', '>=', $dari?->startOfDay()))
            ->when($sampai, fn ($q) => $q->where('created_at', '<=', $sampai?->endOfDay()))
            ->latest('id'), 20);
    }

    private function tanggal(mixed $nilai): ?Carbon
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $nilai) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
