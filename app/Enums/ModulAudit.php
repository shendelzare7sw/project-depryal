<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Nama log (log_name) audit per modul — dipakai untuk filter halaman Audit Log.
 */
enum ModulAudit: string
{
    case Aset = 'aset';
    case KategoriAset = 'kategori_aset';
    case Kriteria = 'kriteria';
    case Periode = 'periode';
    case Nilai = 'nilai';
    case Keputusan = 'keputusan';
    case Pengguna = 'pengguna';
    case Pengaturan = 'pengaturan';

    public function label(): string
    {
        return match ($this) {
            self::Aset => 'Aset',
            self::KategoriAset => 'Kategori Aset',
            self::Kriteria => 'Kriteria',
            self::Periode => 'Periode',
            self::Nilai => 'Nilai Penilaian',
            self::Keputusan => 'Keputusan',
            self::Pengguna => 'Pengguna',
            self::Pengaturan => 'Pengaturan',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Aset => 'building-office-2',
            self::KategoriAset => 'tag',
            self::Kriteria => 'scale',
            self::Periode => 'calendar-days',
            self::Nilai => 'clipboard-document-check',
            self::Keputusan => 'check-badge',
            self::Pengguna => 'users',
            self::Pengaturan => 'adjustments-horizontal',
        };
    }

    /**
     * Kata kerja Indonesia untuk event Eloquent.
     */
    public static function kataKerja(string $event): string
    {
        return match ($event) {
            'created' => 'ditambahkan',
            'updated' => 'diubah',
            'deleted' => 'dihapus',
            'restored' => 'dipulihkan',
            default => $event,
        };
    }
}
