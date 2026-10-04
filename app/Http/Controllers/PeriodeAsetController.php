<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Periode\KelolaAsetPeriode;
use App\Enums\StatusAset;
use App\Http\Requests\TambahAsetPeriodeRequest;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PeriodeAsetController extends Controller
{
    public function create(PeriodePenilaian $periode): View
    {
        throw_if($periode->isFinal(), DomainException::class, 'Periode final tidak dapat diubah.');
        $aset = Aset::with('kategori')->where('status', StatusAset::Aktif->value)
            ->whereNotIn('id', $periode->aset()->pluck('aset.id'))->orderBy('nama_barang')->get();

        return view('periode.tambah-aset', compact('periode', 'aset'));
    }

    public function store(TambahAsetPeriodeRequest $request, PeriodePenilaian $periode, KelolaAsetPeriode $kelola): RedirectResponse
    {
        $jumlah = $kelola->tambah($periode, array_map('intval', $request->validated('aset_ids')));

        return to_route('periode.show', $periode)->with('success', "{$jumlah} aset ditambahkan ke periode. Lengkapi nilainya.");
    }

    public function destroy(PeriodePenilaian $periode, Aset $aset, KelolaAsetPeriode $kelola): RedirectResponse
    {
        $kelola->keluarkan($periode, $aset);

        return to_route('periode.show', $periode)->with('success', "\"{$aset->nama_barang}\" dikeluarkan dari periode.");
    }
}
