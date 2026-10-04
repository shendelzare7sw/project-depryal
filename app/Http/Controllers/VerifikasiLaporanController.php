<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CekBerkasLaporanRequest;
use App\Models\Laporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Halaman publik (tanpa login) untuk memeriksa keaslian laporan dari kode QR. Berkas tidak dapat diunduh di sini.
 */
class VerifikasiLaporanController extends Controller
{
    public function show(string $kode): Response
    {
        $laporan = Laporan::with(['user', 'periode'])->where('kode_verifikasi', strtoupper($kode))->first();

        return response()->view('verifikasi.show', compact('laporan', 'kode'), $laporan ? 200 : 404);
    }

    public function cek(CekBerkasLaporanRequest $request, string $kode): RedirectResponse
    {
        $laporan = Laporan::where('kode_verifikasi', strtoupper($kode))->firstOrFail();
        $cocok = hash_equals((string) $laporan->sha256, (string) hash_file('sha256', (string) $request->file('berkas')?->getRealPath()));

        return back()->with('hasil_cek', $cocok);
    }
}
