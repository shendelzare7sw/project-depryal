<?php

declare(strict_types=1);

use App\Enums\TindakanAset;
use App\Services\Recommendation\RecommendationResolver;

// Ambang default: pertahankan=66,67 · perbaiki=33,33
function resolver(): RecommendationResolver
{
    return new RecommendationResolver(66.67, 33.33);
}

it('merekomendasikan Pertahankan bila skor >= ambang_pertahankan', function (): void {
    expect(resolver()->resolve(66.67))->toBe(TindakanAset::Pertahankan);
    expect(resolver()->resolve(100.0))->toBe(TindakanAset::Pertahankan);
    expect(resolver()->resolve(80.0))->toBe(TindakanAset::Pertahankan);
});

it('merekomendasikan Hapus bila skor < ambang_perbaiki', function (): void {
    expect(resolver()->resolve(0.0))->toBe(TindakanAset::Hapus);
    expect(resolver()->resolve(33.32))->toBe(TindakanAset::Hapus);
});

it('merekomendasikan Perbaiki bila skor antara ambang_perbaiki dan ambang_pertahankan', function (): void {
    expect(resolver()->resolve(33.33))->toBe(TindakanAset::Perbaiki);
    expect(resolver()->resolve(50.0))->toBe(TindakanAset::Perbaiki);
    expect(resolver()->resolve(66.66))->toBe(TindakanAset::Perbaiki);
});

it('menggunakan ambang custom dengan benar', function (): void {
    $custom = new RecommendationResolver(75.0, 25.0);

    expect($custom->resolve(75.0))->toBe(TindakanAset::Pertahankan);
    expect($custom->resolve(74.99))->toBe(TindakanAset::Perbaiki);
    expect($custom->resolve(25.0))->toBe(TindakanAset::Perbaiki);
    expect($custom->resolve(24.99))->toBe(TindakanAset::Hapus);
});

it('mencocokkan rekomendasi golden dataset dengan ambang default', function (): void {
    $r = resolver();

    // A3 skor_relatif=100 → Pertahankan
    expect($r->resolve(100.0))->toBe(TindakanAset::Pertahankan);
    // A1 skor_relatif=62.65 → Perbaiki
    expect($r->resolve(62.65))->toBe(TindakanAset::Perbaiki);
    // A4 skor_relatif=61.43 → Perbaiki
    expect($r->resolve(61.43))->toBe(TindakanAset::Perbaiki);
    // A2 skor_relatif=0.00 → Hapus
    expect($r->resolve(0.0))->toBe(TindakanAset::Hapus);
});
