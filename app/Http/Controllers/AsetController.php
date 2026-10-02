<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Aset\DeleteAset;
use App\Actions\Aset\SaveAset;
use App\Enums\StatusAset;
use App\Exports\AsetExport;
use App\Http\Requests\StoreAsetRequest;
use App\Http\Requests\UpdateAsetRequest;
use App\Models\Aset;
use App\Models\AsetFoto;
use App\Models\KategoriAset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AsetController extends Controller
{
    public function index(Request $request): View
    {
        $aset = Aset::with('kategori')->filter($request->only(['q', 'kategori', 'status']))
            ->orderBy('kode_barang')->orderBy('nup')->paginate(15)->withQueryString();
        $kategori = KategoriAset::orderBy('nama')->pluck('nama', 'id');

        return view('aset.index', compact('aset', 'kategori'));
    }

    public function create(): View
    {
        $aset = new Aset(['jumlah' => 1, 'status' => StatusAset::Aktif]);
        $kategori = KategoriAset::orderBy('nama')->pluck('nama', 'id');

        return view('aset.create', compact('aset', 'kategori'));
    }

    public function store(StoreAsetRequest $request, SaveAset $save): RedirectResponse
    {
        $aset = $save->execute($request->validated(), $request->file('fotos', []));

        return to_route('aset.show', $aset)->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Aset $aset): View
    {
        $aset->load(['kategori', 'fotos']);

        return view('aset.show', compact('aset'));
    }

    public function edit(Aset $aset): View
    {
        $aset->load('fotos');
        $kategori = KategoriAset::orderBy('nama')->pluck('nama', 'id');

        return view('aset.edit', compact('aset', 'kategori'));
    }

    public function update(UpdateAsetRequest $request, Aset $aset, SaveAset $save): RedirectResponse
    {
        $save->execute($request->validated(), $request->file('fotos', []), $aset);

        return to_route('aset.show', $aset)->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Aset $aset, DeleteAset $delete): RedirectResponse
    {
        $delete->execute($aset);

        return to_route('aset.index')->with('success', 'Aset berhasil dihapus.');
    }

    public function destroyFoto(Aset $aset, AsetFoto $foto): RedirectResponse
    {
        Storage::disk('public')->delete($foto->path);
        $foto->delete();

        return to_route('aset.show', $aset)->with('success', 'Foto aset berhasil dihapus.');
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new AsetExport, 'data-aset-bmd-'.now()->format('Ymd').'.xlsx');
    }
}
