<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Pengguna\HubungkanTelegram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilTelegramController extends Controller
{
    public function mulai(Request $request, HubungkanTelegram $hubungkan): RedirectResponse
    {
        $request->session()->put('telegram_tautan', $hubungkan->mulai());

        return to_route('profil.edit');
    }

    public function cek(Request $request, HubungkanTelegram $hubungkan): RedirectResponse
    {
        $hubungkan->selesaikan($request->user(), $request->session()->get('telegram_tautan.kode'));
        $request->session()->forget('telegram_tautan');

        return to_route('profil.edit')->with('success', 'Telegram terhubung. Pesan konfirmasi sudah dikirim.');
    }

    public function putus(Request $request): RedirectResponse
    {
        $request->user()->update(['telegram_chat_id' => null]);
        $request->session()->forget('telegram_tautan');

        return to_route('profil.edit')->with('success', 'Telegram diputuskan. Notifikasi tidak lagi dikirim ke Telegram.');
    }
}
