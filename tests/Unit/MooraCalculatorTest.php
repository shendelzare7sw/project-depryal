<?php

declare(strict_types=1);

use App\Enums\TipeKriteria;
use App\Services\Moora\MooraCalculator;
use App\Services\Moora\MooraInput;

// ── Golden Dataset (docs/01-PRODUCT-SPEC §6) ─────────────────────────────────
// Bobot: Fungsi 0,40 (benefit) · Efektivitas 0,35 (benefit) · Biaya 0,25 (cost)
// Penyebut: Fungsi √54 = 7,348469 · Efektivitas √54 = 7,348469 · Biaya √30 = 5,477226
// Expected Yi: A1=0,2693 · A2=0,0215 · A3=0,4170 · A4=0,2645 (toleransi 1e-4)
// Skor relatif: A1=62,65 · A2=0,00 · A3=100,00 · A4=61,43
// Ranking: A3=1 · A1=2 · A4=3 · A2=4

function goldenCriteria(): array
{
    return [
        1 => ['id' => 1, 'tipe' => TipeKriteria::Benefit, 'bobot' => 0.40], // Fungsi
        2 => ['id' => 2, 'tipe' => TipeKriteria::Benefit, 'bobot' => 0.35], // Efektivitas
        3 => ['id' => 3, 'tipe' => TipeKriteria::Cost,    'bobot' => 0.25], // Biaya
    ];
}

function goldenMatrix(): array
{
    return [
        101 => [1 => 4.0, 2 => 3.0, 3 => 2.0], // A1
        102 => [1 => 2.0, 2 => 2.0, 3 => 4.0], // A2
        103 => [1 => 5.0, 2 => 4.0, 3 => 1.0], // A3
        104 => [1 => 3.0, 2 => 5.0, 3 => 3.0], // A4
    ];
}

it('menghasilkan Yi yang sesuai golden dataset (toleransi 1e-4)', function (): void {
    $result = (new MooraCalculator)->calculate(new MooraInput(goldenCriteria(), goldenMatrix()));

    expect(abs($result->rows[101]['yi'] - 0.2693))->toBeLessThan(1e-4);
    expect(abs($result->rows[102]['yi'] - 0.0215))->toBeLessThan(1e-4);
    expect(abs($result->rows[103]['yi'] - 0.4170))->toBeLessThan(1e-4);
    expect(abs($result->rows[104]['yi'] - 0.2645))->toBeLessThan(1e-4);
});

it('menghasilkan skor_relatif yang benar (toleransi 0,1)', function (): void {
    $result = (new MooraCalculator)->calculate(new MooraInput(goldenCriteria(), goldenMatrix()));

    expect(abs($result->rows[101]['skor_relatif'] - 62.65))->toBeLessThan(0.1);
    expect(abs($result->rows[102]['skor_relatif'] - 0.0))->toBeLessThan(0.1);
    expect(abs($result->rows[103]['skor_relatif'] - 100.0))->toBeLessThan(0.1);
    expect(abs($result->rows[104]['skor_relatif'] - 61.43))->toBeLessThan(0.1);
});

it('menghasilkan ranking yang benar dari golden dataset', function (): void {
    $result = (new MooraCalculator)->calculate(new MooraInput(goldenCriteria(), goldenMatrix()));

    expect($result->rows[103]['ranking'])->toBe(1); // A3
    expect($result->rows[101]['ranking'])->toBe(2); // A1
    expect($result->rows[104]['ranking'])->toBe(3); // A4
    expect($result->rows[102]['ranking'])->toBe(4); // A2
});

it('menghasilkan ranking kembar bila Yi sama', function (): void {
    $criteria = [
        1 => ['id' => 1, 'tipe' => TipeKriteria::Benefit, 'bobot' => 1.0],
    ];
    // Nilai sama untuk A & B → Yi identik → ranking keduanya 1
    $matrix = [
        1 => [1 => 3.0],
        2 => [1 => 3.0],
        3 => [1 => 2.0],
    ];

    $result = (new MooraCalculator)->calculate(new MooraInput($criteria, $matrix));

    expect($result->rows[1]['ranking'])->toBe(1);
    expect($result->rows[2]['ranking'])->toBe(1);
    expect($result->rows[3]['ranking'])->toBe(3); // competition ranking: langsung 3 (bukan 2)
});

it('menangani penyebut nol dengan aman (semua nilai 0)', function (): void {
    $criteria = [
        1 => ['id' => 1, 'tipe' => TipeKriteria::Benefit, 'bobot' => 1.0],
    ];
    $matrix = [
        1 => [1 => 0.0],
        2 => [1 => 0.0],
    ];

    $result = (new MooraCalculator)->calculate(new MooraInput($criteria, $matrix));

    // Penyebut nol → normalisasi 0 → Yi 0 → skor_relatif 100 semua (Ymax=Ymin)
    expect($result->rows[1]['yi'])->toBe(0.0);
    expect($result->rows[2]['yi'])->toBe(0.0);
    expect($result->rows[1]['skor_relatif'])->toBe(100.0);
    expect($result->rows[2]['skor_relatif'])->toBe(100.0);
});

it('menangani 2 alternatif (batas minimum)', function (): void {
    $criteria = [
        1 => ['id' => 1, 'tipe' => TipeKriteria::Benefit, 'bobot' => 0.6],
        2 => ['id' => 2, 'tipe' => TipeKriteria::Cost,    'bobot' => 0.4],
    ];
    $matrix = [
        1 => [1 => 5.0, 2 => 1.0],
        2 => [1 => 1.0, 2 => 5.0],
    ];

    $result = (new MooraCalculator)->calculate(new MooraInput($criteria, $matrix));

    expect($result->rows)->toHaveCount(2);
    expect($result->rows[1]['ranking'])->toBe(1);
    expect($result->rows[2]['ranking'])->toBe(2);
    expect($result->rows[1]['skor_relatif'])->toBe(100.0);
    expect($result->rows[2]['skor_relatif'])->toBe(0.0);
});

it('menyertakan detail normalisasi dan weighted pada output', function (): void {
    $result = (new MooraCalculator)->calculate(new MooraInput(goldenCriteria(), goldenMatrix()));

    $detail = $result->rows[103]['detail']; // A3
    expect($detail)->toHaveKey('normalized');
    expect($detail)->toHaveKey('weighted');
    expect($detail)->toHaveKey('benefit_sum');
    expect($detail)->toHaveKey('cost_sum');

    // A3 normalized Fungsi ≈ 5/7,3485 ≈ 0,6804
    expect(abs($detail['normalized'][1] - 0.6804))->toBeLessThan(1e-3);
    // A3 weighted Fungsi ≈ 0,40 * 0,6804 ≈ 0,2722
    expect(abs($detail['weighted'][1] - 0.2722))->toBeLessThan(1e-3);
});
