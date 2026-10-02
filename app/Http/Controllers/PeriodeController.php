<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PeriodePenilaian;
use Illuminate\View\View;

class PeriodeController extends Controller
{
    public function index(): View
    {
        $periode = PeriodePenilaian::latest()->paginate(10);

        return view('periode.index', compact('periode'));
    }

    public function show(PeriodePenilaian $periode): View
    {
        return view('periode.show', compact('periode'));
    }
}
