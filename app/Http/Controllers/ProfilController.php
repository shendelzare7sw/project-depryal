<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profil.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfilRequest $request): RedirectResponse
    {
        $request->user()->update($request->dataProfil());

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
