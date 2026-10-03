<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModulAudit;
use App\Enums\StatusPeriode;
use App\Models\Concerns\TercatatAudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property StatusPeriode $status
 * @property Carbon|null $tanggal_mulai
 * @property Carbon|null $tanggal_selesai
 * @property Carbon|null $dihitung_pada
 * @property Carbon|null $difinalisasi_pada
 * @property array<int, array<string, mixed>>|null $snapshot_kriteria
 * @property array<string, float>|null $snapshot_ambang
 */
class PeriodePenilaian extends Model
{
    use HasFactory, TercatatAudit;

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
     * Progres pengisian nilai terhadap kriteria aktif saat ini.
     *
     * @return array{terisi: int, diperlukan: int, aset_lengkap: int, total_aset: int, persen: int}
     */
    public function progres(): array
    {
        $kriteriaIds = Kriteria::where('is_active', true)->pluck('id');
        $totalAset = $this->aset()->count();
        $perAset = $this->nilaiKriteria()->whereIn('kriteria_id', $kriteriaIds)
            ->selectRaw('aset_id, COUNT(*) as jumlah')->groupBy('aset_id')->pluck('jumlah', 'aset_id');
        $diperlukan = $totalAset * $kriteriaIds->count();
        $terisi = (int) $perAset->sum();

        return [
            'terisi' => $terisi,
            'diperlukan' => $diperlukan,
            'aset_lengkap' => $kriteriaIds->isEmpty() ? 0 : $perAset->filter(fn ($n) => (int) $n >= $kriteriaIds->count())->count(),
            'total_aset' => $totalAset,
            'persen' => $diperlukan > 0 ? (int) floor($terisi / $diperlukan * 100) : 0,
        ];
    }

    public function isLengkap(): bool
    {
        $p = $this->progres();

        return $p['diperlukan'] > 0 && $p['terisi'] >= $p['diperlukan'];
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

    public function modulAudit(): ModulAudit
    {
        return ModulAudit::Periode;
    }

    public function labelAudit(): string
    {
        return $this->nama;
    }
}
