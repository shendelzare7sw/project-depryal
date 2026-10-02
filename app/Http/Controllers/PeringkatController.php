<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StatusPeriode;
use App\Models\Aset;
use App\Models\PeriodePenilaian;
use App\Services\Peringkat\PeringkatData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeringkatController extends Controller
{
    public function index(Request $request, PeriodePenilaian $periode, PeringkatData $data): View
    {
        return view('peringkat.index', $data->index($periode, $request->only(['q', 'rekomendasi', 'keputusan'])));
    }

    /**
     * Menu "Hasil MOORA": buka periode terbaru yang sudah dihitung/final.
     */
    public function redirectOrIndex(): View|RedirectResponse
    {
        $periode = PeriodePenilaian::whereIn('status', [StatusPeriode::Dihitung->value, StatusPeriode::Final->value])->latest('id')->first()
            ?? PeriodePenilaian::latest('id')->first();

        return $periode ? to_route('peringkat.index', $periode) : view('peringkat.kosong');
    }

    public function show(PeriodePenilaian $periode, Aset $aset, PeringkatData $data): View
    {
        return view('peringkat.show', $data->aset($periode, $aset));
    }

    public function detail(PeriodePenilaian $periode, PeringkatData $data): View
    {
        return view('peringkat.detail-perhitungan', $data->perhitungan($periode));
    }
}
