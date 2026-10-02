<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusPeriode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property StatusPeriode $status
 */
class PeriodePenilaian extends Model
{
    use HasFactory;

    protected $table = 'periode_penilaian';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'snapshot_kriteria',
        'snapshot_ambang',
        'dihitung_pada',
        'dihitung_oleh',
        'difinalisasi_pada',
        'difinalisasi_oleh',
        'alasan_buka_kembali',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusPeriode::class,
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'snapshot_kriteria' => 'array',
            'snapshot_ambang' => 'array',
            'dihitung_pada' => 'datetime',
            'difinalisasi_pada' => 'datetime',
        ];
    }

    public function isFinal(): bool
    {
        return $this->status === StatusPeriode::Final;
    }

    /**
     * @param  Builder<PeriodePenilaian>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', '!=', StatusPeriode::Final->value);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dihitungOlehUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dihitung_oleh');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function difinalisasiOlehUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'difinalisasi_oleh');
    }

    /**
     * @return BelongsToMany<Aset, $this>
     */
    public function aset(): BelongsToMany
    {
        return $this->belongsToMany(Aset::class, 'periode_aset', 'periode_id', 'aset_id')
            ->withPivot('deskripsi_kondisi')
            ->withTimestamps();
    }

    /**
     * @return HasMany<NilaiKriteriaAset, $this>
     */
    public function nilaiKriteria(): HasMany
    {
        return $this->hasMany(NilaiKriteriaAset::class, 'periode_id');
    }

    /**
     * @return HasMany<HasilMoora, $this>
     */
    public function hasilMoora(): HasMany
    {
        return $this->hasMany(HasilMoora::class, 'periode_id')->orderBy('ranking');
    }

    /**
     * @return HasMany<Keputusan, $this>
     */
    public function keputusan(): HasMany
    {
        return $this->hasMany(Keputusan::class, 'periode_id');
    }

    /**
     * @return HasMany<Laporan, $this>
     */
    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'periode_id');
    }
}
