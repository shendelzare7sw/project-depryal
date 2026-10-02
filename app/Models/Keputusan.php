<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TindakanAset;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keputusan extends Model
{
    use HasFactory;

    protected $table = 'keputusan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'periode_id',
        'aset_id',
        'user_id',
        'tindakan',
        'rekomendasi_sistem',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tindakan' => TindakanAset::class,
            'rekomendasi_sistem' => TindakanAset::class,
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
