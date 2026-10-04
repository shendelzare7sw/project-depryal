<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GantiPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GantiPasswordController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        return $request->user()->must_change_password ? view('auth.ganti-password') : to_route('dashboard');
    }

    public function update(GantiPasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password'), 'must_change_password' => false]);

        return to_route('dashboard')->with('success', 'Kata sandi berhasil diganti. Selamat bekerja!');
    }
}
