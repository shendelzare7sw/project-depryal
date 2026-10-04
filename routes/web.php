<?php

declare(strict_types=1);

use App\Http\Controllers\AsetController;
use App\Http\Controllers\AsetImportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\GantiPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\LupaPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriAsetController;
use App\Http\Controllers\KeputusanController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\PeringkatController;
use App\Http\Controllers\PeriodeAsetController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\ProfilController;
use App\Models\PeriodePenilaian;
use Illuminate\Support\Facades\Route;

Route::model('periode', PeriodePenilaian::class);
Route::pattern('aset', '[0-9]+');
Route::pattern('kriteria', '[0-9]+');
Route::pattern('kategori_aset', '[0-9]+');
Route::pattern('periode', '[0-9]+');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/lupa-password', [LupaPasswordController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [LupaPasswordController::class, 'store'])->name('password.email')->middleware('throttle:tamu');
    Route::get('/reset-password/{token}', [LupaPasswordController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [LupaPasswordController::class, 'update'])->name('password.update')->middleware('throttle:tamu');
});

Route::middleware(['auth', 'active', 'wajib-ganti-password'])->group(function (): void {
    Route::get('/ganti-password', [GantiPasswordController::class, 'edit'])->name('password.ganti');
    Route::put('/ganti-password', [GantiPasswordController::class, 'update'])->name('password.ganti.update');
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::get('/notifikasi/{id}', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    // Admin + Operator + Pimpinan (Read-only access)
    Route::middleware('role:admin,operator,pimpinan')->group(function (): void {
        Route::get('periode', [PeriodeController::class, 'index'])->name('periode.index');
        Route::get('periode/{periode}', [PeriodeController::class, 'show'])->name('periode.show');
        Route::get('periode/{periode}/peringkat', [PeringkatController::class, 'index'])->name('peringkat.index');
        Route::get('periode/{periode}/peringkat/{aset}', [PeringkatController::class, 'show'])->name('peringkat.show')->withTrashed();
        Route::get('periode/{periode}/detail-perhitungan', [PeringkatController::class, 'detail'])->name('peringkat.detail');
        Route::get('hasil', [PeringkatController::class, 'redirectOrIndex'])->name('hasil.index');

        Route::resource('aset', AsetController::class)->only(['index', 'show']);
        Route::resource('kriteria', KriteriaController::class)->only(['index'])->parameters(['kriteria' => 'kriteria']);
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/{laporan}/unduh', [LaporanController::class, 'download'])->name('laporan.download')->whereNumber('laporan');
    });

    // [LANE-B] Operator only: aset CUD, import/export, kategori, kriteria CUD, periode create, penilaian, hitung
    Route::middleware('role:operator')->group(function (): void {
        Route::get('aset/export', [AsetController::class, 'export'])->name('aset.export')->middleware('throttle:berat');
        Route::get('aset/import', [AsetImportController::class, 'create'])->name('aset.import');
        Route::post('aset/import', [AsetImportController::class, 'store'])->name('aset.import.store')->middleware('throttle:berat');
        Route::get('aset/import/template', [AsetImportController::class, 'template'])->name('aset.import.template');
        Route::get('aset/import/pratinjau', [AsetImportController::class, 'preview'])->name('aset.import.preview');
        Route::post('aset/import/simpan', [AsetImportController::class, 'confirm'])->name('aset.import.confirm')->middleware('throttle:berat');
        Route::delete('aset/{aset}/foto/{foto}', [AsetController::class, 'destroyFoto'])->name('aset.foto.destroy')->scopeBindings();

        Route::resource('aset', AsetController::class)->except(['index', 'show']);
        Route::resource('kategori-aset', KategoriAsetController::class)->except(['show']);
        Route::resource('kriteria', KriteriaController::class)->except(['index', 'show'])->parameters(['kriteria' => 'kriteria']);

        // Fase 3: periode & input penilaian
        Route::get('periode/create', [PeriodeController::class, 'create'])->name('periode.create');
        Route::post('periode', [PeriodeController::class, 'store'])->name('periode.store');
        Route::get('periode/{periode}/edit', [PeriodeController::class, 'edit'])->name('periode.edit');
        Route::put('periode/{periode}', [PeriodeController::class, 'update'])->name('periode.update');
        Route::post('periode/{periode}/hitung', [PeriodeController::class, 'hitung'])->name('periode.hitung')->middleware('throttle:berat');
        Route::post('periode/{periode}/buka-kembali', [PeriodeController::class, 'bukaKembali'])->name('periode.buka-kembali');
        Route::delete('periode/{periode}', [PeriodeController::class, 'destroy'])->name('periode.destroy');
        Route::get('periode/{periode}/aset/tambah', [PeriodeAsetController::class, 'create'])->name('periode.aset.create');
        Route::post('periode/{periode}/aset', [PeriodeAsetController::class, 'store'])->name('periode.aset.store');
        Route::delete('periode/{periode}/aset/{aset}', [PeriodeAsetController::class, 'destroy'])->name('periode.aset.destroy');
        Route::get('periode/{periode}/penilaian/{aset}', [PenilaianController::class, 'edit'])->name('penilaian.edit');
        Route::put('periode/{periode}/penilaian/{aset}', [PenilaianController::class, 'update'])->name('penilaian.update');
    });

    // [LANE-C] Pimpinan only: keputusan.edit/update, periode.finalisasi
    Route::middleware('role:pimpinan')->group(function (): void {
        Route::get('keputusan', [KeputusanController::class, 'index'])->name('keputusan.index');
        Route::get('periode/{periode}/keputusan/{aset}', [KeputusanController::class, 'edit'])->name('keputusan.edit')->withTrashed();
        Route::put('periode/{periode}/keputusan/{aset}', [KeputusanController::class, 'update'])->name('keputusan.update')->withTrashed();
        Route::post('periode/{periode}/finalisasi', [KeputusanController::class, 'finalisasi'])->name('periode.finalisasi');
    });

    // Laporan: dibuat oleh Operator & Pimpinan (Admin hanya melihat/mengunduh)
    Route::middleware('role:operator,pimpinan')->group(function (): void {
        Route::post('laporan', [LaporanController::class, 'store'])->name('laporan.store')->middleware('throttle:berat');
    });

    // [LANE-E] Admin only: pengguna, pengaturan, audit-log
    Route::middleware('role:admin')->group(function (): void {
        Route::resource('pengguna', PenggunaController::class)->except(['show', 'destroy'])->parameters(['pengguna' => 'user'])->whereNumber('user');
        Route::post('pengguna/{user}/status', [PenggunaController::class, 'status'])->name('pengguna.status')->whereNumber('user');
        Route::post('pengguna/{user}/reset-password', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset-password')->whereNumber('user')->middleware('throttle:berat');
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });
});
