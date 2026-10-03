# 02 — Arsitektur (MVC bersih)

## 1. Struktur Folder
```
app/
├── Actions/                      # satu class = satu use case (method tunggal `execute`)
│   ├── Periode/  CreatePeriode, CalculatePeriode, FinalizePeriode, ReopenPeriode
│   ├── Penilaian/ SaveNilaiAset
│   └── Keputusan/ SaveKeputusan
├── Enums/                        # UserRole, TipeKriteria, TindakanAset, StatusPeriode, StatusAset, JenisLaporan
├── Exports/                      # AsetExport, AsetTemplateExport, LaporanExport (multi-sheet) + LaporanSheet
├── Imports/                      # AsetImport (maatwebsite)
├── Http/
│   ├── Controllers/              # TIPIS. satu controller per resource
│   │   ├── Auth/ LoginController, LogoutController
│   │   ├── DashboardController, AsetController, AsetImportController, KategoriAsetController
│   │   ├── KriteriaController, PeriodeController, PenilaianController
│   │   ├── PeringkatController, KeputusanController, LaporanController
│   │   └── PenggunaController, PengaturanController, AuditLogController, ProfilController
│   ├── Middleware/ EnsureRole (alias `role`), EnsureUserIsActive
│   └── Requests/                 # FormRequest per aksi: StoreAsetRequest, UpdateKriteriaRequest, ...
├── Models/                       # Aset, KategoriAset, AsetFoto, Kriteria, KriteriaSkala, PeriodePenilaian,
│                                 # NilaiKriteriaAset, HasilMoora, Keputusan, Laporan, Pengaturan, User
├── Policies/                     # PeriodePenilaianPolicy, AsetPolicy, KeputusanPolicy, UserPolicy
├── Services/
│   ├── Moora/ MooraCalculator, MooraInput, MooraResult   # PURE PHP, tanpa DB
│   ├── Recommendation/ RecommendationResolver
│   ├── Dashboard/ AdminDashboard, OperatorDashboard, PimpinanDashboard   # ->data(): array untuk view dashboard/{role}
│   └── Laporan/ LaporanGenerator
├── Support/ Rupiah.php (format helper), Setting.php (akses tabel pengaturan ber-cache)
└── View/Components/              # hanya jika komponen butuh logika PHP (mis. SidebarMenu); selain itu anonymous

database/
├── migrations/                   # satu migrasi per tabel, urut sesuai §2
├── factories/                    # semua model
└── seeders/ DatabaseSeeder, UserSeeder, KriteriaSeeder, PengaturanSeeder, KategoriAsetSeeder, DemoAsetSeeder

resources/views/
├── layouts/
│   ├── app.blade.php             # shell: appbar + drawer + bottom-nav + slot + <x-flash>
│   ├── guest.blade.php           # layout login
│   └── partials/ assets.blade.php (SEMUA CDN), sidebar.blade.php, bottom-nav.blade.php
├── components/                   # anonymous Blade components (lihat §5)
│   ├── ui/ card, badge-tindakan, badge-status, stat, empty-state, responsive-list, page-header, progress-meter
│   ├── form/ field, input, select, textarea, file, scale-radio
│   ├── confirm-form.blade.php    # SweetAlert konfirmasi
│   ├── flash.blade.php           # SweetAlert toast
│   └── rupiah.blade.php
├── auth/ login.blade.php
├── dashboard/ admin, pimpinan, operator
├── aset/ index, create, edit, show, import, _form
├── kategori-aset/ index, create, edit, _form
├── kriteria/ index, create, edit, _form
├── periode/ index, create, show
├── penilaian/ edit
├── peringkat/ index, show, detail-perhitungan
├── keputusan/ edit
├── laporan/ index, create, pdf/ (template dompdf: peringkat, keputusan, lengkap)
├── pengguna/ index, create, edit, _form
├── pengaturan/ edit
├── audit-log/ index
└── profil/ edit

routes/web.php                    # grup per role, lihat §4
tests/
├── Unit/ MooraCalculatorTest, RecommendationResolverTest
└── Feature/ AuthTest, RoleAccessTest, AsetImportTest, KriteriaBobotTest, PeriodeFlowTest, KeputusanTest, LaporanTest
```
**Aturan penempatan:** validasi → `Requests`; aturan akses → `Policies`/middleware; use case multi-langkah → `Actions`; perhitungan murni → `Services`; query berulang → scope Model; presentasi berulang → Blade component.

## 2. Skema Database (urutan migrasi)
Konvensi: `id` bigIncrements, `timestamps()`, FK `constrained()->restrictOnDelete()` kecuali disebut cascade. Kolom enum disimpan sebagai `string` + cast Enum di Model (bukan kolom ENUM MySQL).

| # | Tabel | Kolom penting |
|---|---|---|
| 1 | `users` | name, **username** (unique), email (nullable, unique), password, **role** (string/UserRole), is_active (bool, default true), nip (nullable), jabatan (nullable), remember_token |
| 2 | `kategori_aset` | kode (unique), nama |
| 3 | `aset` | kategori_aset_id, **kode_barang**, **nup** (nomor urut pendaftaran, int), nama_barang, jumlah (int, def 1), luas (decimal 12,2 null), tanggal_perolehan (date), harga_satuan (decimal 18,2), nilai_perolehan (decimal 18,2), umur_ekonomis (int), akumulasi_penyusutan (decimal 18,2), sisa_ueb (int), nilai_buku (decimal 18,2), lokasi (null), status (StatusAset: aktif/dalam_perbaikan/diusulkan_hapus), softDeletes. **Unique (kode_barang, nup)** — kode barang BMD dipakai bersama oleh banyak unit sejenis (mis. 32 Tugu), jadi NUP yang membedakan; importer mengisi NUP berurutan bila kolom tidak ada. |
| 4 | `kriteria` | kode (C1..), nama, tipe (TipeKriteria), bobot (decimal 5,4), skala_min (tinyint def 1), skala_maks (tinyint def 5), urutan, is_active, keterangan (null) |
| 5 | `kriteria_skala` | kriteria_id (cascade), nilai (tinyint), label, deskripsi (null); unique (kriteria_id, nilai) — rubrik 1–5 |
| 6 | `periode_penilaian` | nama, tanggal_mulai, tanggal_selesai (null), status (StatusPeriode), snapshot_kriteria (json null), snapshot_ambang (json null), dihitung_pada, dihitung_oleh (FK users null), difinalisasi_pada, difinalisasi_oleh (FK users null), alasan_buka_kembali (null), created_by |
| 7 | `periode_aset` | periode_id (cascade), aset_id, **deskripsi_kondisi** (text null); unique (periode_id, aset_id) |
| 8 | `aset_foto` | aset_id (cascade), periode_id (null, FK), path, keterangan (null) |
| 9 | `nilai_kriteria_aset` | periode_id (cascade), aset_id, kriteria_id, nilai (decimal 8,2); unique (periode_id, aset_id, kriteria_id) |
| 10 | `hasil_moora` | periode_id (cascade), aset_id, yi (decimal 12,6), skor_relatif (decimal 6,2), ranking (unsignedInt), rekomendasi (TindakanAset), detail (json); unique (periode_id, aset_id) |
| 11 | `keputusan` | periode_id (cascade), aset_id, user_id, tindakan (TindakanAset), rekomendasi_sistem (TindakanAset), catatan (text null); unique (periode_id, aset_id) |
| 12 | `laporan` | user_id, periode_id (null), jenis (JenisLaporan), format (pdf/xlsx), nama_file, path |
| 13 | `pengaturan` | key (unique), value (text); kunci: `ambang_pertahankan`=66.67, `ambang_perbaiki`=33.33, `nama_instansi`, `alamat_instansi`, `nama_penandatangan`, `nip_penandatangan`, `jabatan_penandatangan` |
| 14 | `activity_log` | dari `spatie/laravel-activitylog` |

> Perbedaan terhadap laporan Bab III (dokumentasikan di Bab III revisi): `foto`/`deskripsi_kondisi` dipindah ke `aset_foto` & `periode_aset` agar punya histori per periode; ditambah `kategori_aset`, `periode_penilaian`, `periode_aset`, `kriteria_skala`, `pengaturan`.

### Relasi Eloquent
`Aset` belongsTo `KategoriAset`; hasMany `AsetFoto`; belongsToMany `PeriodePenilaian` (pivot `periode_aset`, `withPivot('deskripsi_kondisi')`).
`PeriodePenilaian` hasMany `NilaiKriteriaAset`, `HasilMoora`, `Keputusan`.
`Kriteria` hasMany `KriteriaSkala`, `NilaiKriteriaAset`.
`HasilMoora`/`Keputusan` belongsTo `Aset`, `PeriodePenilaian`; `Keputusan` belongsTo `User`.

## 3. Enum (kontrak nama — jangan diubah sepihak)
```php
UserRole:      Admin='admin', Operator='operator', Pimpinan='pimpinan'
TipeKriteria:  Benefit='benefit', Cost='cost'
TindakanAset:  Pertahankan='pertahankan', Perbaiki='perbaiki', Hapus='hapus'   // + label(), color()
StatusPeriode: Draft='draft', Dinilai='dinilai', Dihitung='dihitung', Final='final'
StatusAset:    Aktif='aktif', DalamPerbaikan='dalam_perbaikan', DiusulkanHapus='diusulkan_hapus'
JenisLaporan:  Peringkat='peringkat', Keputusan='keputusan', Lengkap='lengkap'
FormatLaporan: Pdf='pdf', Xlsx='xlsx'
```
Setiap Enum punya `label(): string` (Bahasa Indonesia) dan, bila relevan, `color(): string` (nama varian DaisyUI: success/warning/error/info/ghost).

## 4. Routing (`routes/web.php`)
```php
Route::middleware('guest')->group(fn () => /* GET/POST /login */);
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');
    Route::resource('profil', ProfilController::class)->only(['edit','update'])->parameters(['profil'=>'user']);

    // Admin + Operator + Pimpinan (read)
    Route::middleware('role:admin,operator,pimpinan')->group(function () {
        Route::get('periode', [PeriodeController::class,'index'])->name('periode.index');
        Route::get('periode/{periode}', [PeriodeController::class,'show'])->name('periode.show');
        Route::get('periode/{periode}/peringkat', [PeringkatController::class,'index'])->name('peringkat.index');
        Route::get('periode/{periode}/peringkat/{aset}', [PeringkatController::class,'show'])->name('peringkat.show');
        Route::get('periode/{periode}/detail-perhitungan', [PeringkatController::class,'detail'])->name('peringkat.detail');
        Route::resource('aset', AsetController::class)->only(['index','show']);
        Route::resource('kriteria', KriteriaController::class)->only(['index']);
    });
    Route::middleware('role:operator,pimpinan,admin')->group(fn () => /* laporan.index/create/download */);

    // [LANE-B] Operator only: aset CUD, import/export, kategori, kriteria CUD, periode create, penilaian, hitung
    // [LANE-C] Pimpinan only: keputusan.edit/update, periode.finalisasi
    // [LANE-E] Admin only: pengguna, pengaturan, audit-log
});
```
Nama parameter route-model-binding: `{periode}` → `PeriodePenilaian` (definisikan `Route::model('periode', PeriodePenilaian::class)`), `{aset}`, `{kriteria}`.

## 5. Komponen UI (kontrak antar-lane)
Semua anonymous component (`resources/views/components`). Props ringkas, **tanpa JS selain atribut Alpine pendek**.
**Pola pemakaian (layout, daftar, form, gerbang screenshot) wajib mengikuti `docs/05-UI-PATTERNS.md`.**

| Komponen | Props | Fungsi |
|---|---|---|
| `<x-layouts.app title subtitle>` | | shell: sidebar `brand-950` (menu dari `App\Support\Navigation`), topbar (judul + notifikasi + menu pengguna), bottom-nav ponsel. Konten tanpa `max-w` |
| `<x-ui.hero title eyebrow? subtitle?>` + slot `actions`, `aside` | | kartu gradien untuk dashboard/halaman kunci |
| `<x-ui.stat-grid :items :columns=4>` | items: `label, value, icon, tone, meta?, href?` | 2 kolom ponsel, 3/4/6 desktop; ikon selalu tampil |
| `<x-ui.card title? subtitle? icon? iconTone? link? linkLabel? flush?>` + slot `actions`, `chip` | | kartu ber-ikon; `flush` untuk tabel/daftar |
| `<x-ui.btn tone icon? href? type? compact?>` | tone: primary, dark, neutral, white, soft-*, glass, amber | tombol h-10; `compact` = ikon saja di ponsel |
| `<x-ui.table-action tone icon label href? type?>` | tone: view, edit, delete, success, neutral | tombol ikon 36 px tabel desktop |
| `<x-ui.badge tone dot?>` | tone: success, warning, error, info, primary, secondary, accent, neutral, ghost | badge lembut |
| `<x-ui.badge-tindakan :tindakan>` / `<x-ui.badge-status :status>` / `<x-ui.badge-perhatian :aset>` | | badge dari `->color()` & `->label()`; ⚠ sisa UEB ≤ 3 |
| `<x-ui.empty-state icon title text>` + slot aksi | | keadaan kosong |
| `<x-ui.progress-meter :value :max label strict?>` | | `progress` + teks x/y; `strict` = hijau hanya bila tepat max |
| `<x-ui.detail-item label>` / `<x-ui.rubrik-list :kriteria>` | | pasangan label–nilai; rubrik skala lipat |
| `<x-ui.notification-bell :items :unread>` / `<x-ui.user-menu>` | | lonceng notifikasi & menu pengguna topbar |
| `<x-form.field name label hint? required?>` | | label + slot input + pesan error `@error` |
| `<x-form.input/select/textarea/file>` | `name`, `value`, atribut lain via `$attributes` | input bergaya konsisten (fokus `brand`) |
| `<x-form.scale-radio :kriteria :value>` | | radio besar 1–5 + rubrik (`kriteria_skala`) |
| `<x-confirm-form :action method title text confirm icon danger?>` | | **form + SweetAlert** (merah untuk DELETE) |
| `<x-flash/>` | | toast SweetAlert dari `session('success'|'error')` |
| `<x-rupiah :value/>` | | `Rp 1.234.567,89` |

Notifikasi dalam aplikasi: tabel `notifications` (Laravel), `App\Notifications\SistemNotification(judul, pesan, url, icon, tone)`, dikirim via `App\Services\Notifikasi::kirimKeRole(roles, notifikasi, kecuali)`. Route `notifikasi.index|baca|baca-semua`.

## 6. Referensi implementasi aset CDN & SweetAlert (boleh disalin apa adanya)
`layouts/partials/assets.blade.php` (pin versi saat Lane 0 — cek versi stabil terbaru; DaisyUI 5 + Tailwind 4 browser build mendukung CDN):
```blade
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```
`components/confirm-form.blade.php`:
```blade
@props(['title' => 'Yakin?', 'text' => '', 'confirm' => 'Ya, lanjutkan', 'icon' => 'warning', 'method' => 'POST'])
<form {{ $attributes->merge(['method' => 'POST']) }} x-data="{ busy: false }"
      @submit.prevent="Swal.fire({ title: @js($title), text: @js($text), icon: @js($icon),
        showCancelButton: true, confirmButtonText: @js($confirm), cancelButtonText: 'Batal', reverseButtons: true })
        .then(r => { if (r.isConfirmed) { busy = true; $el.submit() } })">
    @csrf
    @if (strtoupper($method) !== 'POST') @method($method) @endif
    {{ $slot }}
</form>
```
`components/flash.blade.php`:
```blade
@php($msg = session('success') ?? session('error'))
@if ($msg)
<div x-data x-init="Swal.fire({ toast: true, position: 'top', timer: 3000, showConfirmButton: false,
     icon: @js(session('success') ? 'success' : 'error'), title: @js($msg) })"></div>
@endif
```
Pemakaian: `<x-confirm-form :action="route('aset.destroy',$aset)" method="DELETE" title="Hapus aset?" text="Data tidak dapat dikembalikan." icon="warning"> <button class="btn btn-error btn-sm">Hapus</button> </x-confirm-form>`.

> Catatan jujur: Tailwind *browser/Play CDN* ditujukan untuk pengembangan; untuk produksi resmi disarankan build. Karena seluruh aset terpusat di `assets.blade.php`, migrasi ke Vite nanti hanya mengubah satu file. Tidak perlu dikerjakan sekarang.

## 7. Kontrak Service / Action
```php
// Services/Moora — pure
final class MooraCalculator {
    public function calculate(MooraInput $input): MooraResult;  // lihat 01-PRODUCT-SPEC §4
}
final readonly class MooraInput  { /** @param array<int,array{id:int,tipe:TipeKriteria,bobot:float}> $criteria
                                       @param array<int,array<int,float>> $matrix [aset_id][kriteria_id] */ }
final readonly class MooraResult { /** @var array<int,array{yi:float,skor_relatif:float,ranking:int,detail:array}> $rows keyed by aset_id */ }

// Services/Recommendation
final class RecommendationResolver { public function resolve(float $skorRelatif): TindakanAset; } // baca Setting::ambang

// Actions/Periode
CreatePeriode::execute(array $data): PeriodePenilaian          // validasi: tidak ada periode non-final lain
CalculatePeriode::execute(PeriodePenilaian $p, User $by): void  // transaksi: cek bobot=1, nilai lengkap, ≥2 aset, hitung, simpan HasilMoora, snapshot, status=dihitung
FinalizePeriode::execute(PeriodePenilaian $p, User $by): void   // cek semua aset punya Keputusan, kunci, update Aset.status
ReopenPeriode::execute(PeriodePenilaian $p, User $by, string $alasan): void

// Actions
SaveNilaiAset::execute(PeriodePenilaian $p, Aset $a, array $nilai, ?string $kondisi, array $fotos): void  // status → dinilai bila lengkap semua
SaveKeputusan::execute(PeriodePenilaian $p, Aset $a, User $by, TindakanAset $t, ?string $catatan): Keputusan

// Services/Laporan
LaporanGenerator::generate(JenisLaporan $j, FormatLaporan $f, PeriodePenilaian $p, User $by): Laporan  // berkas di disk privat local/laporan, riwayat di tabel laporan
```
Aksi yang melanggar state machine melempar `DomainException` → ditangkap handler global → redirect back + toast error (satu tempat di `bootstrap/app.php`).

## 8. Pola Controller (contoh target kualitas)
```php
public function store(StoreKriteriaRequest $request): RedirectResponse
{
    Kriteria::create($request->validated());
    return to_route('kriteria.index')->with('success', 'Kriteria ditambahkan.');
}
```
Jika method lebih dari ±7 baris → pindahkan ke Action.
