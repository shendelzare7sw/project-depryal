<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModulAudit;
use App\Models\Concerns\TercatatAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property int|null $aset_count
 */
class KategoriAset extends Model
{
    use HasFactory, TercatatAudit;

    protected $table = 'kategori_aset';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'nama',
    ];

    /**
     * @return HasMany<Aset, $this>
     */
    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'kategori_aset_id');
    }

    public function modulAudit(): ModulAudit
    {
        return ModulAudit::KategoriAset;
    }

    public function labelAudit(): string
    {
        return $this->nama;
    }
}
