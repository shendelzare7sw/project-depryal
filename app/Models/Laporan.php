<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FormatLaporan;
use App\Enums\JenisLaporan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $periode_id
 * @property JenisLaporan $jenis
 * @property FormatLaporan $format
 * @property string $nama_file
 * @property string $path
 */
class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'periode_id',
        'jenis',
        'format',
        'nama_file',
        'path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis' => JenisLaporan::class,
            'format' => FormatLaporan::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<PeriodePenilaian, $this>
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePenilaian::class, 'periode_id');
    }
}
