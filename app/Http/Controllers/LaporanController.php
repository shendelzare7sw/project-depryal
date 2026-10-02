<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(): View
    {
        $laporan = Laporan::latest()->paginate(10);

        return view('laporan.index', compact('laporan'));
    }
}
