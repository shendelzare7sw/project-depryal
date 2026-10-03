<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Jejak perubahan data (Admin, read-only).
 */
class AuditLogController extends Controller
{
    public function index(Request $request, AuditLogQuery $query): View
    {
        $aktivitas = $query->cari($request->only(['user', 'modul', 'dari', 'sampai']));
        $pengguna = User::orderBy('name')->pluck('name', 'id');

        return view('audit-log.index', compact('aktivitas', 'pengguna'));
    }
}
