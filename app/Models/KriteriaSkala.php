<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KriteriaSkala extends Model
{
    use HasFactory;

    protected $table = 'kriteria_skala';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kriteria_id',
        'nilai',
        'label',
        'deskripsi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Kriteria, $this>
     */
    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }
}
