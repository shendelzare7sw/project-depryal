<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Periode\CreatePeriode;
use App\Actions\Periode\DeletePeriode;
use App\Actions\Periode\HitungPeriode;
use App\Actions\Periode\ReopenPeriode;
use App\Enums\StatusAset;
use App\Http\Requests\ReopenPeriodeRequest;
use App\Http\Requests\StorePeriodeRequest;
use App\Http\Requests\UpdatePeriodeRequest;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\PeriodePenilaian;
use App\Services\Periode\PeriodeDetail;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodeController extends Controller
{
    public function index(): View
    {
        $periode = PeriodePenilaian::withCount('aset')->latest('id')->paginate(10);
        $adaAktif = PeriodePenilaian::aktif()->exists();

        return view('periode.index', compact('periode', 'adaAktif'));
    }

    public function create(): View
    {
        $kategori = KategoriAset::withCount(['aset' => fn ($q) => $q->where('status', StatusAset::Aktif->value)])->orderBy('nama')->get();
        $aset = Aset::with('kategori')->where('status', StatusAset::Aktif->value)->orderBy('nama_barang')->get();

        return view('periode.create', compact('kategori', 'aset'));
    }

    public function store(StorePeriodeRequest $request, CreatePeriode $create): RedirectResponse
    {
        $periode = $create->execute($request->validated() + ['created_by' => $request->user()->id]);

        return to_route('periode.show', $periode)->with('success', "Periode dibuat dengan {$periode->aset()->count()} aset. Mulai input nilai.");
    }

    public function show(Request $request, PeriodePenilaian $periode, PeriodeDetail $detail): View
    {
        return view('periode.show', $detail->halaman($periode, $request->only(['q', 'status'])));
    }

    public function edit(PeriodePenilaian $periode): View
    {
        return view('periode.edit', compact('periode'));
    }

    public function update(UpdatePeriodeRequest $request, PeriodePenilaian $periode): RedirectResponse
    {
        throw_if($periode->isFinal(), DomainException::class, 'Periode final tidak dapat diubah.');
        $periode->update($request->validated());

        return to_route('periode.show', $periode)->with('success', 'Data periode diperbarui.');
    }

    public function hitung(Request $request, PeriodePenilaian $periode, HitungPeriode $hitung): RedirectResponse
    {
        $hitung->execute($periode, $request->user());

        return to_route('peringkat.index', $periode)->with('success', 'Perhitungan MOORA selesai. Peringkat & rekomendasi diperbarui.');
    }

    public function bukaKembali(ReopenPeriodeRequest $request, PeriodePenilaian $periode, ReopenPeriode $reopen): RedirectResponse
    {
        $reopen->execute($periode, $request->user(), $request->validated('alasan'));

        return to_route('periode.show', $periode)->with('success', 'Periode dibuka kembali. Pimpinan dapat meninjau ulang keputusan.');
    }

    public function destroy(PeriodePenilaian $periode, DeletePeriode $delete): RedirectResponse
    {
        $delete->execute($periode);

        return to_route('periode.index')->with('success', "Periode \"{$periode->nama}\" dihapus.");
    }
}
