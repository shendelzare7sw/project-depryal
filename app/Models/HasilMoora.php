<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TindakanAset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilMoora extends Model
{
    use HasFactory;

    protected $table = 'hasil_moora';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'periode_id',
        'aset_id',
        'yi',
        'skor_relatif',
        'ranking',
        'rekomendasi',
        'detail',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'yi' => 'float',
            'skor_relatif' => 'float',
            'ranking' => 'integer',
            'rekomendasi' => TindakanAset::class,
            'detail' => 'array',
        ];
    }

    /**
     * @return BelongsTo<PeriodePenilaian, $this>
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }

    /**
     * @return BelongsTo<Aset, $this>
     */
    public function aset(): BelongsTo
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }
}
