<?php

declare(strict_types=1);

namespace App\Services\Aset;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Simpan foto aset ke disk public dalam bentuk JPEG terkompresi (GD bawaan PHP, tanpa paket baru).
 * Sisi terpanjang maks. 1600 px, kualitas 80, orientasi EXIF kamera ponsel diluruskan.
 * Bila GD tidak dapat membaca berkas, berkas asli disimpan apa adanya.
 */
final class FotoAset
{
    public const SISI_MAKS = 1600;

    public const KUALITAS = 80;

    public function simpan(UploadedFile $foto, string $folder): string
    {
        $gambar = $this->baca($foto);

        if (! $gambar) {
            return (string) $foto->store($folder, 'public');
        }

        $gambar = $this->perkecil($this->luruskan($gambar, $foto));
        $path = $folder.'/'.Str::random(40).'.jpg';

        ob_start();
        imagejpeg($gambar, null, self::KUALITAS);
        Storage::disk('public')->put($path, (string) ob_get_clean());

        return $path;
    }

    private function baca(UploadedFile $foto): ?GdImage
    {
        $isi = @file_get_contents((string) $foto->getRealPath());
        $gambar = $isi === false ? false : @imagecreatefromstring($isi);

        return $gambar instanceof GdImage ? $gambar : null;
    }

    private function luruskan(GdImage $gambar, UploadedFile $foto): GdImage
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data((string) $foto->getRealPath()) : false;
        $sudut = match ((int) (is_array($exif) ? ($exif['Orientation'] ?? 1) : 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $sudut === 0 ? $gambar : (imagerotate($gambar, $sudut, 0) ?: $gambar);
    }

    private function perkecil(GdImage $gambar): GdImage
    {
        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);
        $skala = min(1, self::SISI_MAKS / max($lebar, $tinggi));

        $hasil = imagecreatetruecolor(max(1, (int) round($lebar * $skala)), max(1, (int) round($tinggi * $skala)));
        imagefill($hasil, 0, 0, (int) imagecolorallocate($hasil, 255, 255, 255));
        imagecopyresampled($hasil, $gambar, 0, 0, 0, 0, imagesx($hasil), imagesy($hasil), $lebar, $tinggi);

        return $hasil;
    }
}
