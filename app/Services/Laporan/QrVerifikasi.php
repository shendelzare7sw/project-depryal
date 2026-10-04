<?php

declare(strict_types=1);

namespace App\Services\Laporan;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Kode & gambar QR verifikasi keaslian laporan. Gambar PNG (data URI) agar dapat dirender dompdf.
 */
final class QrVerifikasi
{
    public function kodeBaru(): string
    {
        return strtoupper(bin2hex(random_bytes(8)));
    }

    public function url(string $kode): string
    {
        return route('verifikasi.show', $kode);
    }

    public function gambar(string $kode): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => true,
            'scale' => 6,
            'quietzoneSize' => 1,
        ]);

        return (new QRCode($options))->render($this->url($kode));
    }
}
