<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $pengaturan = Pengaturan::all();

        return view('pengaturan.index', compact('pengaturan'));
    }
}
