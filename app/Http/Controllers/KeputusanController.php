<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Keputusan\SaveKeputusan;
use App\Actions\Periode\FinalizePeriode;
use App\Enums\StatusPeriode;
use App\Enums\TindakanAset;
use App\Http\Requests\SaveKeputusanRequest;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use App\Services\Peringkat\PeringkatData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Penentuan tindakan & finalisasi periode (Pimpinan).
 */
class KeputusanController extends Controller
{
    /**
     * Menu "Keputusan": daftar aset yang belum diputuskan pada periode yang sedang dihitung.
     */
    public function index(): RedirectResponse
    {
        $periode = PeriodePenilaian::where('status', StatusPeriode::Dihitung->value)->latest('id')->first();

        return $periode ? to_route('peringkat.index', [$periode, 'keputusan' => 'belum']) : to_route('hasil.index');
    }

    public function edit(PeriodePenilaian $periode, Aset $aset, PeringkatData $data): View
    {
        return view('keputusan.edit', $data->aset($periode, $aset));
    }

    public function update(SaveKeputusanRequest $request, PeriodePenilaian $periode, Aset $aset, SaveKeputusan $save, PeringkatData $data): RedirectResponse
    {
        $keputusan = $save->execute($periode, $aset, $request->user(), TindakanAset::from($request->validated('tindakan')), $request->validated('catatan'));
        $berikutnya = $data->belumDiputuskanBerikutnya($periode);
        $pesan = "{$aset->nama_barang}: {$keputusan->tindakan->label()} tersimpan.";

        return $berikutnya
            ? to_route('keputusan.edit', [$periode, $berikutnya])->with('success', $pesan.' Lanjut ke aset berikutnya.')
            : to_route('peringkat.index', $periode)->with('success', $pesan.' Semua aset sudah diputuskan — periode siap difinalisasi.');
    }

    public function finalisasi(Request $request, PeriodePenilaian $periode, FinalizePeriode $finalize): RedirectResponse
    {
        $finalize->execute($periode, $request->user());

        return to_route('peringkat.index', $periode)->with('success', 'Periode difinalisasi. Status aset diperbarui sesuai keputusan.');
    }
}
