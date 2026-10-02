<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Menu navigasi per role (sumber tunggal untuk sidebar & bottom nav mobile).
 * Item yang route-nya belum ada otomatis disembunyikan (fase berikutnya cukup menambah route).
 */
final class Navigation
{
    /**
     * @return list<array{label: string, items: list<array{label: string, route: string, icon: string, active: string, mobile: bool}>}>
     */
    public static function for(User $user): array
    {
        $groups = match ($user->role) {
            UserRole::Admin => [
                ['Ringkasan', [self::item('Beranda', 'dashboard', 'home', 'dashboard', true)]],
                ['Sistem', [
                    self::item('Pengguna', 'pengguna.index', 'users', 'pengguna.*', true),
                    self::item('Pengaturan', 'pengaturan.index', 'adjustments-horizontal', 'pengaturan.*'),
                    self::item('Audit Log', 'audit-log.index', 'clipboard-document-list', 'audit-log.*'),
                ]],
                ['Data & Penilaian', [
                    self::item('Data Aset', 'aset.index', 'building-office-2', 'aset.*', true),
                    self::item('Kriteria', 'kriteria.index', 'scale', 'kriteria.*'),
                    self::item('Periode Penilaian', 'periode.index', 'calendar-days', 'periode.*|penilaian.*'),
                    self::item('Hasil MOORA', 'hasil.index', 'chart-bar', 'hasil.*|peringkat.*'),
                    self::item('Laporan', 'laporan.index', 'document-chart-bar', 'laporan.*'),
                ]],
            ],
            UserRole::Operator => [
                ['Ringkasan', [self::item('Beranda', 'dashboard', 'home', 'dashboard', true)]],
                ['Master Data', [
                    self::item('Data Aset', 'aset.index', 'building-office-2', 'aset.*', true),
                    self::item('Kategori Aset', 'kategori-aset.index', 'tag', 'kategori-aset.*'),
                    self::item('Kriteria', 'kriteria.index', 'scale', 'kriteria.*'),
                ]],
                ['Penilaian', [
                    self::item('Periode Penilaian', 'periode.index', 'calendar-days', 'periode.*|penilaian.*', true),
                    self::item('Hasil MOORA', 'hasil.index', 'chart-bar', 'hasil.*|peringkat.*'),
                    self::item('Laporan', 'laporan.index', 'document-chart-bar', 'laporan.*'),
                ]],
            ],
            UserRole::Pimpinan => [
                ['Ringkasan', [self::item('Beranda', 'dashboard', 'home', 'dashboard', true)]],
                ['Keputusan', [
                    self::item('Peringkat Aset', 'hasil.index', 'chart-bar', 'hasil.*|peringkat.*', true),
                    self::item('Keputusan', 'keputusan.index', 'check-badge', 'keputusan.*', true),
                    self::item('Laporan', 'laporan.index', 'document-chart-bar', 'laporan.*'),
                ]],
            ],
        };

        $result = [];

        foreach ($groups as [$label, $items]) {
            $items = array_values(array_filter($items, fn (array $i): bool => Route::has($i['route'])));

            if ($items !== []) {
                $result[] = ['label' => $label, 'items' => $items];
            }
        }

        return $result;
    }

    /**
     * Item untuk bottom navigation mobile (maks 3; slot ke-4 = tombol Menu).
     *
     * @return list<array{label: string, route: string, icon: string, active: string, mobile: bool}>
     */
    public static function mobile(User $user): array
    {
        $items = array_merge(...array_column(self::for($user), 'items'));

        return array_slice(array_values(array_filter($items, fn (array $i): bool => $i['mobile'])), 0, 3);
    }

    /**
     * @return array{label: string, route: string, icon: string, active: string, mobile: bool}
     */
    private static function item(string $label, string $route, string $icon, string $active, bool $mobile = false): array
    {
        return compact('label', 'route', 'icon', 'active', 'mobile');
    }
}
