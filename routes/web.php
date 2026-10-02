<?php

declare(strict_types=1);

use App\Http\Controllers\AsetController;
use App\Http\Controllers\AsetImportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriAsetController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PeringkatController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\ProfilController;
use App\Models\PeriodePenilaian;
use Illuminate\Support\Facades\Route;

Route::model('periode', PeriodePenilaian::class);
Route::pattern('aset', '[0-9]+');
Route::pattern('kriteria', '[0-9]+');
Route::pattern('kategori_aset', '[0-9]+');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // Admin + Operator + Pimpinan (Read-only access)
    Route::middleware('role:admin,operator,pimpinan')->group(function (): void {
        Route::get('periode', [PeriodeController::class, 'index'])->name('periode.index');
        Route::get('periode/{periode}', [PeriodeController::class, 'show'])->name('periode.show');
        Route::get('periode/{periode}/peringkat', [PeringkatController::class, 'index'])->name('peringkat.index');
        Route::get('periode/{periode}/peringkat/{aset}', [PeringkatController::class, 'show'])->name('peringkat.show');
        Route::get('periode/{periode}/detail-perhitungan', [PeringkatController::class, 'detail'])->name('peringkat.detail');
        Route::get('hasil', [PeringkatController::class, 'redirectOrIndex'])->name('hasil.index');

        Route::resource('aset', AsetController::class)->only(['index', 'show']);
        Route::resource('kriteria', KriteriaController::class)->only(['index'])->parameters(['kriteria' => 'kriteria']);
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    });

    // [LANE-B] Operator only: aset CUD, import/export, kategori, kriteria CUD, periode create, penilaian, hitung
    Route::middleware('role:operator')->group(function (): void {
        Route::get('aset/export', [AsetController::class, 'export'])->name('aset.export');
        Route::get('aset/import', [AsetImportController::class, 'create'])->name('aset.import');
        Route::post('aset/import', [AsetImportController::class, 'store'])->name('aset.import.store');
        Route::get('aset/import/template', [AsetImportController::class, 'template'])->name('aset.import.template');
        Route::get('aset/import/pratinjau', [AsetImportController::class, 'preview'])->name('aset.import.preview');
        Route::post('aset/import/simpan', [AsetImportController::class, 'confirm'])->name('aset.import.confirm');
        Route::delete('aset/{aset}/foto/{foto}', [AsetController::class, 'destroyFoto'])->name('aset.foto.destroy')->scopeBindings();

        Route::resource('aset', AsetController::class)->except(['index', 'show']);
        Route::resource('kategori-aset', KategoriAsetController::class)->except(['show']);
        Route::resource('kriteria', KriteriaController::class)->except(['index', 'show'])->parameters(['kriteria' => 'kriteria']);
    });

    // [LANE-C] Pimpinan only: keputusan.edit/update, periode.finalisasi

    // [LANE-E] Admin only: pengguna, pengaturan, audit-log
    Route::middleware('role:admin')->group(function (): void {
        Route::get('pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    });
});
