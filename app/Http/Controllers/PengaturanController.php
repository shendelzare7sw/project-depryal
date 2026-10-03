<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Pengaturan\SimpanPengaturan;
use App\Http\Requests\UpdatePengaturanRequest;
use App\Support\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Pengaturan sistem: ambang rekomendasi & data instansi/penandatangan laporan (Admin).
 */
class PengaturanController extends Controller
{
    public function index(): View
    {
        return view('pengaturan.index', ['pengaturan' => Setting::all()]);
    }

    public function update(UpdatePengaturanRequest $request, SimpanPengaturan $simpan): RedirectResponse
    {
        $simpan->execute($request->validated(), $request->user());

        return to_route('pengaturan.index')->with('success', 'Pengaturan disimpan. Ambang baru berlaku untuk perhitungan berikutnya.');
    }
}
