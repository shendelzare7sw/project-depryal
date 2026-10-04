<?php

declare(strict_types=1);

use App\Services\Aset\FotoAset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('foto besar diperkecil ke sisi maksimum dan disimpan sebagai JPEG', function () {
    Storage::fake('public');
    $foto = UploadedFile::fake()->image('kamera.png', 3200, 2400);

    $path = app(FotoAset::class)->simpan($foto, 'aset/1');

    Storage::disk('public')->assertExists($path);
    [$lebar, $tinggi, $tipe] = getimagesizefromstring(Storage::disk('public')->get($path));
    expect($path)->toEndWith('.jpg')
        ->and([$lebar, $tinggi])->toBe([1600, 1200])
        ->and($tipe)->toBe(IMAGETYPE_JPEG);
});

test('foto kecil tidak diperbesar', function () {
    Storage::fake('public');

    $path = app(FotoAset::class)->simpan(UploadedFile::fake()->image('kecil.jpg', 800, 600), 'aset/1');

    expect(array_slice(getimagesizefromstring(Storage::disk('public')->get($path)), 0, 2))->toBe([800, 600]);
});
