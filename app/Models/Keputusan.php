<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModulAudit;
use App\Enums\TindakanAset;
use App\Models\Concerns\TercatatAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $periode_id
 * @property int $aset_id
 * @property int $user_id
 * @property TindakanAset $tindakan
 * @property TindakanAset $rekomendasi_sistem
 * @property string|null $catatan
 */
class Keputusan extends Model
{
    use HasFactory, TercatatAudit;

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

    public function modulAudit(): ModulAudit
    {
        return ModulAudit::Keputusan;
    }

    public function labelAudit(): string
    {
        return 'periode #'.$this->periode_id.', aset #'.$this->aset_id.' → '.$this->tindakan->label();
    }
}
