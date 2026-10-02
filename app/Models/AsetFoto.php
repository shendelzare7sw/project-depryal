<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $path
 * @property-read string $url
 */
class AsetFoto extends Model
{
    use HasFactory;

    protected $table = 'aset_foto';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'aset_id',
        'periode_id',
        'path',
        'keterangan',
    ];

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * @return BelongsTo<Aset, $this>
     */
    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    /**
     * @return BelongsTo<PeriodePenilaian, $this>
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }
}
