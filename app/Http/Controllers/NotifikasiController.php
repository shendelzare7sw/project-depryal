<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Tampil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $notifikasi = Tampil::ambil($request->user()->notifications(), 20);

        return view('notifikasi.index', compact('notifikasi'));
    }

    /**
     * Tandai dibaca lalu buka tautan notifikasi (jika ada).
     */
    public function baca(Request $request, string $id): RedirectResponse
    {
        $notifikasi = $request->user()->notifications()->findOrFail($id);
        $notifikasi->markAsRead();

        return redirect($notifikasi->data['url'] ?? route('notifikasi.index'));
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
