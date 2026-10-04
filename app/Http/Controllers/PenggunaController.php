<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Pengguna\KelolaAkunPengguna;
use App\Enums\UserRole;
use App\Http\Requests\StorePenggunaRequest;
use App\Http\Requests\UpdatePenggunaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manajemen akun pengguna (Admin).
 */
class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::filter($request->only(['q', 'role', 'status']))->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        return view('pengguna.index', compact('users'));
    }

    public function create(): View
    {
        return view('pengguna.create', ['user' => new User(['role' => UserRole::Operator, 'is_active' => true])]);
    }

    public function store(StorePenggunaRequest $request): RedirectResponse
    {
        $user = User::create($request->validated() + ['must_change_password' => true]);

        return to_route('pengguna.index')->with('success', "Akun {$user->username} dibuat.");
    }

    public function edit(User $user): View
    {
        return view('pengguna.edit', compact('user'));
    }

    public function update(UpdatePenggunaRequest $request, User $user): RedirectResponse
    {
        $user->update($request->safe()->except(['password']));

        return to_route('pengguna.index')->with('success', "Akun {$user->username} diperbarui.");
    }

    public function status(Request $request, User $user, KelolaAkunPengguna $kelola): RedirectResponse
    {
        $kelola->ubahStatus($user, $request->user());

        return back()->with('success', "Akun {$user->username} ".($user->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function resetPassword(Request $request, User $user, KelolaAkunPengguna $kelola): RedirectResponse
    {
        $password = $kelola->resetPassword($user, $request->user());

        return back()->with('success', "Password {$user->username} direset.")->with('password_baru', ['username' => $user->username, 'password' => $password]);
    }
}
