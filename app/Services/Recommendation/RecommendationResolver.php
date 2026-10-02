<?php

declare(strict_types=1);

namespace App\Services\Recommendation;

use App\Enums\TindakanAset;
use App\Support\Setting;

/**
 * Menentukan rekomendasi tindakan berdasarkan skor_relatif dan ambang dari pengaturan.
 *
 * Aturan (docs/01-PRODUCT-SPEC §3 aturan #5):
 *   skor_relatif ≥ ambang_pertahankan  → Pertahankan
 *   skor_relatif < ambang_perbaiki     → Hapus
 *   selain itu                         → Perbaiki
 *
 * Ambang diinjeksi lewat constructor agar mudah di-test tanpa DB.
 */
final class RecommendationResolver
{
    public function __construct(
        private readonly float $ambangPertahankan,
        private readonly float $ambangPerbaiki,
    ) {}

    /**
     * Buat instance dari tabel pengaturan (via Setting helper).
     */
    public static function fromSetting(): self
    {
        return new self(
            Setting::ambangPertahankan(),
            Setting::ambangPerbaiki(),
        );
    }

    public function resolve(float $skorRelatif): TindakanAset
    {
        if ($skorRelatif >= $this->ambangPertahankan) {
            return TindakanAset::Pertahankan;
        }

        if ($skorRelatif < $this->ambangPerbaiki) {
            return TindakanAset::Hapus;
        }

        return TindakanAset::Perbaiki;
    }
}
