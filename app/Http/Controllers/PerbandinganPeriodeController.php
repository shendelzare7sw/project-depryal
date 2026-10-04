<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PerbandinganPeriodeRequest;
use App\Services\Peringkat\RiwayatPenilaian;
use Illuminate\View\View;

/**
 * Tren peringkat: bandingkan hasil MOORA dua periode (default: dua periode terbaru yang sudah dihitung).
 */
class PerbandinganPeriodeController extends Controller
{
    public function __invoke(PerbandinganPeriodeRequest $request, RiwayatPenilaian $riwayat): View
    {
        $pilihan = $riwayat->periodeBerhasil();
        $baru = $pilihan->firstWhere('id', (int) $request->validated('baru')) ?? $pilihan->first();
        $sebelumBaru = $pilihan->get((int) $pilihan->search(fn ($p) => $p->is($baru)) + 1);
        $lama = $pilihan->firstWhere('id', (int) $request->validated('lama')) ?? $sebelumBaru;

        return view('peringkat.perbandingan', compact('pilihan', 'lama', 'baru') + ['data' => $lama && $baru ? $riwayat->perbandingan($lama, $baru) : null]);
    }
}
