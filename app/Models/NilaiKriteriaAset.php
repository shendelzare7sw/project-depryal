<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModulAudit;
use App\Models\Concerns\TercatatAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiKriteriaAset extends Model
{
    use HasFactory, TercatatAudit;

    protected $table = 'nilai_kriteria_aset';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'periode_id',
        'aset_id',
        'kriteria_id',
        'nilai',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'float',
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
     * @return BelongsTo<Kriteria, $this>
     */
    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(Kriteria::class, 'kriteria_id');
    }

    public function modulAudit(): ModulAudit
    {
        return ModulAudit::Nilai;
    }

    public function labelAudit(): string
    {
        return 'periode #'.$this->periode_id.', aset #'.$this->aset_id.', kriteria #'.$this->kriteria_id.' = '.$this->nilai;
    }
}
