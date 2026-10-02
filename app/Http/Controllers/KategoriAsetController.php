<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\KategoriAset\DeleteKategoriAset;
use App\Http\Requests\StoreKategoriAsetRequest;
use App\Http\Requests\UpdateKategoriAsetRequest;
use App\Models\KategoriAset;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriAsetController extends Controller
{
    public function index(): View
    {
        $kategori = KategoriAset::withCount('aset')->orderBy('nama')->get();

        return view('kategori-aset.index', compact('kategori'));
    }

    public function create(): View
    {
        return view('kategori-aset.create', ['kategori' => new KategoriAset]);
    }

    public function store(StoreKategoriAsetRequest $request): RedirectResponse
    {
        KategoriAset::create($request->validated());

        return to_route('kategori-aset.index')->with('success', 'Kategori aset ditambahkan.');
    }

    public function edit(KategoriAset $kategoriAset): View
    {
        return view('kategori-aset.edit', ['kategori' => $kategoriAset]);
    }

    public function update(UpdateKategoriAsetRequest $request, KategoriAset $kategoriAset): RedirectResponse
    {
        $kategoriAset->update($request->validated());

        return to_route('kategori-aset.index')->with('success', 'Kategori aset diperbarui.');
    }

    public function destroy(KategoriAset $kategoriAset, DeleteKategoriAset $delete): RedirectResponse
    {
        $delete->execute($kategoriAset);

        return to_route('kategori-aset.index')->with('success', 'Kategori aset dihapus.');
    }
}
