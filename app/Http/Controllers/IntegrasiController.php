<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Integrasi\SimpanIntegrasi;
use App\Actions\Integrasi\UjiIntegrasi;
use App\Http\Requests\UpdateIntegrasiRequest;
use App\Support\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menu Integrasi (Admin): Telegram Bot & SMTP email disimpan di database, bukan di .env.
 */
class IntegrasiController extends Controller
{
    public function index(): View
    {
        return view('integrasi.index', ['pengaturan' => Setting::all()]);
    }

    public function update(UpdateIntegrasiRequest $request, SimpanIntegrasi $simpan): RedirectResponse
    {
        $simpan->execute($request->validated(), $request->user());

        return to_route('integrasi.index')->with('success', 'Pengaturan integrasi disimpan.');
    }

    public function uji(Request $request, string $kanal, UjiIntegrasi $uji): RedirectResponse
    {
        return back()->with('success', $uji->execute($kanal, $request->user()));
    }
}
