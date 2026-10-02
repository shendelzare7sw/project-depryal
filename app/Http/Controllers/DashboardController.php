<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\PeriodePenilaian;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_aset' => Aset::count(),
            'periode_aktif' => PeriodePenilaian::aktif()->count(),
            'total_periode' => PeriodePenilaian::count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
