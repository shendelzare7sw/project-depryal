<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Kriteria\DeleteKriteria;
use App\Actions\Kriteria\SaveKriteria;
use App\Enums\TipeKriteria;
use App\Http\Requests\StoreKriteriaRequest;
use App\Http\Requests\UpdateKriteriaRequest;
use App\Models\Kriteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KriteriaController extends Controller
{
    public function index(): View
    {
        $kriteria = Kriteria::with('skala')->withPemakaian()->orderBy('urutan')->get();
        $totalBobot = Kriteria::totalBobotAktifPersen();

        return view('kriteria.index', compact('kriteria', 'totalBobot'));
    }

    public function create(): View
    {
        $kriteria = new Kriteria([
            'tipe' => TipeKriteria::Benefit, 'bobot' => 0, 'skala_min' => 1, 'skala_maks' => 5,
            'urutan' => (int) Kriteria::max('urutan') + 1, 'is_active' => true,
        ]);

        return view('kriteria.create', compact('kriteria'));
    }

    public function store(StoreKriteriaRequest $request, SaveKriteria $save): RedirectResponse
    {
        $save->execute($request->validated());

        return to_route('kriteria.index')->with('success', 'Kriteria ditambahkan.');
    }

    public function edit(Kriteria $kriteria): View
    {
        $kriteria->load('skala');

        return view('kriteria.edit', compact('kriteria'));
    }

    public function update(UpdateKriteriaRequest $request, Kriteria $kriteria, SaveKriteria $save): RedirectResponse
    {
        $save->execute($request->validated(), $kriteria);

        return to_route('kriteria.index')->with('success', 'Kriteria diperbarui.');
    }

    public function destroy(Kriteria $kriteria, DeleteKriteria $delete): RedirectResponse
    {
        $delete->execute($kriteria);

        return to_route('kriteria.index')->with('success', 'Kriteria dihapus.');
    }
}
