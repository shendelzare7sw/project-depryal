<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Integrasi;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Klien Telegram Bot API (gratis, resmi) memakai HTTP client Laravel — tanpa paket tambahan.
 * Token tidak pernah dimasukkan ke pesan galat agar tidak bocor ke log/tampilan.
 */
final class Telegram
{
    private const API = 'https://api.telegram.org/bot';

    /**
     * Username bot (tanpa @) dari token; galat bila token tidak valid.
     */
    public function namaBot(string $token): string
    {
        return (string) $this->panggil($token, 'getMe')['username'];
    }

    public function kirim(string $chatId, string $teksHtml, ?string $token = null): void
    {
        $this->panggil($token ?? (string) Integrasi::telegramToken(), 'sendMessage', [
            'chat_id' => $chatId,
            'text' => $teksHtml,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);
    }

    /**
     * Cari chat yang mengirim "/start {kode}" ke bot (alur tautan dari halaman Profil).
     */
    public function cariChatId(string $kode): ?string
    {
        $pembaruan = $this->panggil((string) Integrasi::telegramToken(), 'getUpdates', ['allowed_updates' => ['message']]);

        foreach (array_reverse(is_array($pembaruan) ? $pembaruan : []) as $item) {
            if (trim((string) data_get($item, 'message.text')) === "/start {$kode}") {
                return (string) data_get($item, 'message.chat.id');
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return mixed bagian "result" dari respons Telegram
     */
    private function panggil(string $token, string $metode, array $data = []): mixed
    {
        try {
            $respons = Http::timeout(10)->asJson()->post(self::API.$token.'/'.$metode, $data);
        } catch (Throwable) {
            throw new RuntimeException('Tidak dapat menghubungi server Telegram. Periksa koneksi internet server.');
        }

        if (! $respons->json('ok')) {
            throw new RuntimeException('Telegram menolak permintaan: '.($respons->json('description') ?? 'token tidak valid'));
        }

        return $respons->json('result');
    }
}
