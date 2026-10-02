<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Penilaian\SaveNilaiAset;
use App\Http\Requests\SaveNilaiRequest;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use App\Services\Periode\PeriodeDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Input nilai kriteria per aset dalam satu periode (Operator).
 */
class PenilaianController extends Controller
{
    public function edit(PeriodePenilaian $periode, Aset $aset, PeriodeDetail $detail): View
    {
        abort_unless($periode->aset()->whereKey($aset->id)->exists(), 404);

        return view('penilaian.edit', $detail->penilaian($periode, $aset));
    }

    public function update(SaveNilaiRequest $request, PeriodePenilaian $periode, Aset $aset, SaveNilaiAset $save, PeriodeDetail $detail): RedirectResponse
    {
        $save->execute($periode, $aset, $request->validated('kriteria') ?? [], $request->validated('deskripsi_kondisi'), $request->file('fotos', []));
        $berikutnya = $request->boolean('lanjut') ? $detail->asetBelumLengkapBerikutnya($periode, $aset) : null;

        return $berikutnya
            ? to_route('penilaian.edit', [$periode, $berikutnya])->with('success', "Nilai {$aset->nama_barang} tersimpan. Lanjut ke aset berikutnya.")
            : to_route('periode.show', $periode)->with('success', "Nilai {$aset->nama_barang} tersimpan.");
    }
}
