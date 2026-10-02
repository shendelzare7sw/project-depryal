<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusPeriode;
use App\Enums\TipeKriteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property TipeKriteria $tipe
 * @property float $bobot
 * @property int $skala_min
 * @property int $skala_maks
 * @property int $urutan
 * @property bool $is_active
 * @property string|null $keterangan
 * @property bool|null $dipakai_final
 * @property bool|null $punya_nilai
 */
class Kriteria extends Model
{
    use HasFactory;

    protected $table = 'kriteria';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'bobot',
        'skala_min',
        'skala_maks',
        'urutan',
        'is_active',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipe' => TipeKriteria::class,
            'bobot' => 'float',
            'skala_min' => 'integer',
            'skala_maks' => 'integer',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Kriteria>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('urutan');
    }

    /**
     * Tambahkan flag `dipakai_final` & `punya_nilai` tanpa N+1.
     *
     * @param  Builder<Kriteria>  $query
     */
    public function scopeWithPemakaian(Builder $query): void
    {
        $query->withExists([
            'nilaiAset as dipakai_final' => fn (Builder $q) => $q->whereHas(
                'periode',
                fn (Builder $p) => $p->where('status', StatusPeriode::Final->value),
            ),
            'nilaiAset as punya_nilai',
        ]);
    }

    /**
     * Kriteria yang sudah dipakai periode final: tipe & skala terkunci, tidak bisa dihapus.
     */
    public function isTerkunci(): bool
    {
        return (bool) ($this->dipakai_final ?? $this->nilaiAset()
            ->whereHas('periode', fn (Builder $p) => $p->where('status', StatusPeriode::Final->value))
            ->exists());
    }

    /**
     * Jumlah bobot kriteria aktif dalam persen (target tepat 100).
     */
    public static function totalBobotAktifPersen(): float
    {
        return round((float) static::query()->where('is_active', true)->sum('bobot') * 100, 2);
    }

    public function bobotPersen(): float
    {
        return round($this->bobot * 100, 2);
    }

    /**
     * @return HasMany<KriteriaSkala, $this>
     */
    public function skala(): HasMany
    {
        return $this->hasMany(KriteriaSkala::class, 'kriteria_id')->orderBy('nilai');
    }

    /**
     * @return HasMany<NilaiKriteriaAset, $this>
     */
    public function nilaiAset(): HasMany
    {
        return $this->hasMany(NilaiKriteriaAset::class, 'kriteria_id');
    }
}
