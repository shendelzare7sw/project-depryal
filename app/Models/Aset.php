<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kategori_aset_id
 * @property string $kode_barang
 * @property int $nup
 * @property string $nama_barang
 * @property int $sisa_ueb
 * @property Carbon|null $tanggal_perolehan
 * @property StatusAset $status
 */
class Aset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'aset';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kategori_aset_id',
        'kode_barang',
        'nup',
        'nama_barang',
        'jumlah',
        'luas',
        'tanggal_perolehan',
        'harga_satuan',
        'nilai_perolehan',
        'umur_ekonomis',
        'akumulasi_penyusutan',
        'sisa_ueb',
        'nilai_buku',
        'lokasi',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusAset::class,
            'tanggal_perolehan' => 'date',
            'harga_satuan' => 'decimal:2',
            'nilai_perolehan' => 'decimal:2',
            'akumulasi_penyusutan' => 'decimal:2',
            'nilai_buku' => 'decimal:2',
            'luas' => 'decimal:2',
            'jumlah' => 'integer',
            'umur_ekonomis' => 'integer',
            'sisa_ueb' => 'integer',
            'nup' => 'integer',
        ];
    }

    public function getPerluPerhatianAttribute(): bool
    {
        return $this->sisa_ueb <= 3;
    }

    /**
     * @param  Builder<Aset>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', StatusAset::Aktif->value);
    }

    /**
     * @param  Builder<Aset>  $query
     */
    public function scopePerluPerhatian(Builder $query): void
    {
        $query->where('sisa_ueb', '<=', 3);
    }

    /**
     * Filter daftar aset: q (nama/kode), kategori (id), status (StatusAset).
     *
     * @param  Builder<Aset>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $status = StatusAset::tryFrom((string) ($filters['status'] ?? ''));

        $query
            ->when($q !== '', fn (Builder $b) => $b->where(fn (Builder $w) => $w
                ->where('nama_barang', 'like', "%{$q}%")
                ->orWhere('kode_barang', 'like', "%{$q}%")))
            ->when(filled($filters['kategori'] ?? null), fn (Builder $b) => $b->where('kategori_aset_id', (int) $filters['kategori']))
            ->when($status, fn (Builder $b) => $b->where('status', $status?->value));
    }

    public function isDipakaiPeriodeFinal(): bool
    {
        return $this->periode()->where('status', StatusPeriode::Final->value)->exists();
    }

    /**
     * @return BelongsTo<KategoriAset, $this>
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_aset_id');
    }

    /**
     * @return HasMany<AsetFoto, $this>
     */
    public function fotos(): HasMany
    {
        return $this->hasMany(AsetFoto::class, 'aset_id');
    }

    /**
     * @return BelongsToMany<PeriodePenilaian, $this>
     */
    public function periode(): BelongsToMany
    {
        return $this->belongsToMany(PeriodePenilaian::class, 'periode_aset', 'aset_id', 'periode_id')
            ->withPivot('deskripsi_kondisi')
            ->withTimestamps();
    }

    /**
     * @return HasMany<NilaiKriteriaAset, $this>
     */
    public function nilaiKriteria(): HasMany
    {
        return $this->hasMany(NilaiKriteriaAset::class, 'aset_id');
    }

    /**
     * @return HasMany<HasilMoora, $this>
     */
    public function hasilMoora(): HasMany
    {
        return $this->hasMany(HasilMoora::class, 'aset_id');
    }

    /**
     * @return HasMany<Keputusan, $this>
     */
    public function keputusan(): HasMany
    {
        return $this->hasMany(Keputusan::class, 'aset_id');
    }
}
