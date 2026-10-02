<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->paginate(10);

        return view('pengguna.index', compact('users'));
    }
}
