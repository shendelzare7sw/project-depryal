<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\PeriodePenilaian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PeringkatController extends Controller
{
    public function index(PeriodePenilaian $periode): View
    {
        $hasil = $periode->hasilMoora()->with('aset')->get();

        return view('hasil.index', compact('periode', 'hasil'));
    }

    public function redirectOrIndex(): View|RedirectResponse
    {
        $periode = PeriodePenilaian::latest()->first();

        if ($periode) {
            return redirect()->route('peringkat.index', $periode);
        }

        return view('hasil.index');
    }

    public function show(PeriodePenilaian $periode, Aset $aset): View
    {
        return view('hasil.show', compact('periode', 'aset'));
    }

    public function detail(PeriodePenilaian $periode): View
    {
        return view('hasil.detail', compact('periode'));
    }
}
