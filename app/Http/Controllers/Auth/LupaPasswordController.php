<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Pengguna\ResetPasswordLewatEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LupaPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.lupa-password');
    }

    public function store(Request $request, ResetPasswordLewatEmail $reset): RedirectResponse
    {
        $reset->kirimTautan($request->validate(['email' => ['required', 'email']])['email']);

        return back()->with('status', 'Bila email tersebut terdaftar, tautan reset kata sandi sudah dikirim. Periksa juga folder spam.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email')]);
    }

    public function update(ResetPasswordRequest $request, ResetPasswordLewatEmail $reset): RedirectResponse
    {
        $reset->atur($request->only(['email', 'password', 'password_confirmation', 'token']));

        return to_route('login')->with('success', 'Kata sandi berhasil diatur ulang. Silakan masuk dengan kata sandi baru.');
    }
}
