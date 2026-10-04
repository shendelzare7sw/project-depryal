# PROGRESS

## Status
- [x] Fase 0a Foundation Data (migrasi, model, seeder, middleware) — selesai (2026-10-01)
- [x] Fase 0b Layout, Komponen UI, Auth & Styling — selesai (2026-10-01)
- [x] Refactoring 3 Role: Admin, Operator, Pimpinan — selesai (2026-10-01)
- [x] Fase 1 MOORA Core (Calculator, Resolver, CalculatePeriode Action, Unit & Feature Tests) — selesai (2026-10-01)
- [x] Fase 2 Master Data (Aset + Import/Export Excel, Kriteria, Kategori) — selesai (2026-10-02)
- [x] Perombakan UI + Notifikasi — selesai (2026-10-02)
- [x] Fase 3 Periode dan Input Penilaian — selesai (2026-10-02)
- [x] Fase 4 Peringkat, Keputusan, Finalisasi — selesai (2026-10-03)
- [x] Fase 5 Dashboard dan Laporan PDF/Excel — selesai (2026-10-03)
- [x] Fase 6 Admin (Pengguna, Pengaturan, Audit Log, Profil) — selesai (2026-10-03)
- [x] Fase 7 QA dan Hardening — selesai (2026-10-03)
- [ ] Branch `production-ready` — rekomendasi siap produksi (berjalan, lihat checklist di bawah)

## Checklist `production-ready` (branch terpisah dari `main`)
Setiap butir: kode + test Pest + Pint/Larastan hijau + screenshot 390/1440 (bila ada UI) + commit & push.

🔴 Penting sebelum dipakai sungguhan
- [x] Hapus periode Draft/Dinilai + tambah/keluarkan aset periode — `KelolaPeriodeTest` (9 test) · commit `fbe4779`
- [x] Aset baru ikut periode berjalan (sakelar di form tambah aset) — `KelolaPeriodeTest` · commit `fbe4779`
- [x] Wajib ganti kata sandi login pertama / setelah reset Admin — `KataSandiTest` · commit `c1cd14b`
- [x] Lupa kata sandi lewat email (SMTP) — `KataSandiTest` · commit `c1cd14b`
- [ ] Tampilan tanpa CDN (Tailwind CLI, aset di-host sendiri) + CSP
- [ ] Backup otomatis harian database + foto/laporan

🟡 Pengalaman pengguna
- [x] Kompres foto saat upload (GD, maks 1600 px, JPEG 80) — `FotoAsetTest` · commit `1f98ffb`
- [ ] Riwayat penilaian per aset (skor, rekomendasi, keputusan per periode)
- [ ] Tombol "Perbesar teks" (diingat per perangkat)
- [ ] Logout otomatis saat tidak aktif
- [ ] Uji langsung dengan 1–2 staf kecamatan — *dilakukan user/tim, bukan kode*

🟢 Nilai tambah
- [x] QR verifikasi keaslian laporan PDF + halaman `/verifikasi/{kode}` — `VerifikasiLaporanTest` · commit `be6eea2`
- [x] Notifikasi Telegram (gratis, pengganti WhatsApp) & email; kunci diatur di menu **Integrasi** (Admin), bukan `.env` — `IntegrasiTest` (7 test)
- [ ] Perbandingan antar periode (tren peringkat aset)
- [ ] CI GitHub Actions (`composer check` tiap push)
- [ ] Sinkronisasi dokumen Bab III (use case, ERD, matriks akses)

---

## Catatan serah-terima (terbaru di atas)

### Fase 7 — Lane Z QA & Hardening — 2026-10-03

**Temuan & perbaikan:**
- Bug: progres periode > 100% (nilai aset yang sudah dihapus ikut terhitung) → diperbaiki.
- Keamanan: formula injection pada export Excel → value binder `TeksAmanFormula`.
- Keamanan: header keamanan HTTP + `Cache-Control: no-store` untuk halaman login (tombol Back setelah logout tidak menampilkan data).
- Keamanan: rate limit endpoint berat; login tetap 5 percobaan / menit.
- Integritas: foto bukti periode final tidak dapat dihapus.
- UX: menu Keputusan (pimpinan) kini halaman sendiri; halaman galat bermerek.
- Kode: LoginController ditipiskan (LoginRequest).

**Diaudit tanpa temuan:** XSS (tidak ada output `{!! !!}`), CSRF (semua form POST + @csrf), mass assignment (fillable eksplisit + FormRequest), IDOR (aset di luar periode, notifikasi orang lain, keputusan tanpa hasil → 404/ditolak), upload (gambar ≤4 MB tanpa SVG, Excel ≤12 MB di disk privat), session (regenerasi saat login, invalidasi saat logout, akun nonaktif dikeluarkan).

**Dokumen:** `docs/06-PENGUJIAN-BAB-IV.md` (46 kasus uji + perbandingan manual), `docs/07-ALUR-APLIKASI.md` (alur per peran), README diperbarui (cara jalan, akun demo, checklist produksi).

**Test:** `HardeningTest` (8), `AlurLengkapTest` (alur end-to-end 3 peran). **Total 134 test / 693 asersi hijau.**

**Saran lanjutan (opsional):** build Tailwind/Vite agar CSP bisa dipasang; uji di perangkat HP nyata; backup otomatis.

### Fase 6 — Lane F Admin (Pengguna, Pengaturan, Audit Log, Profil) — 2026-10-03 · TITIK AMAN UNTUK BLACKBOX TESTING

**Selesai:**
- **Pengguna** (`PenggunaController`, `StorePenggunaRequest`/`UpdatePenggunaRequest`, `Actions\Pengguna\KelolaAkunPengguna`): daftar + filter (nama/username/email, peran, status), tambah, ubah, aktif/nonaktif (SweetAlert), reset password acak yang ditampilkan sekali (tombol Salin). Admin tidak bisa menurunkan peran / menonaktifkan / mereset dirinya sendiri.
- **Pengaturan** (`PengaturanController`, `UpdatePengaturanRequest`, `Actions\Pengaturan\SimpanPengaturan`): ambang Pertahankan > Perbaiki (0–100) dengan pratinjau rentang langsung, data instansi (nama, alamat) & penandatangan (nama, jabatan, NIP) untuk laporan.
- **Audit Log** (`AuditLogController`, `Services\AuditLogQuery`, trait `TercatatAudit`, enum `ModulAudit`): filter pengguna/modul/tanggal, paginasi, detail sebelum/sesudah per kolom (lipat).
- **Profil**: validasi dipindah ke `UpdateProfilRequest` (email opsional, ganti password wajib password lama).
- Test `AdminTest` (12). **Total 125 test / 598 asersi hijau**, Pint & Larastan level 5 bersih, `migrate:fresh --seed` lulus.

**Panduan blackbox testing (akun: `admin` / `operator` / `pimpinan`, password `password`):**
1. Operator: Data Aset (tambah 2 tahap + foto, import BMD → pratinjau → simpan, export), Kategori, Kriteria (bobot 100%, rubrik).
2. Operator: Periode Penilaian → Buat Periode → Input Nilai tiap aset (Simpan & Lanjut) → Hitung MOORA.
3. Pimpinan: lonceng notifikasi → Peringkat → Mulai Putuskan (catatan wajib bila beda rekomendasi) → Finalisasi → status aset berubah.
4. Operator/Pimpinan: Laporan PDF/Excel (Peringkat/Keputusan/Lengkap) + riwayat; Admin hanya unduh.
5. Admin: Pengguna, Pengaturan, Audit Log (cek jejak langkah 1–4), dashboard statistik.
6. Operator: buka kembali periode final (alasan wajib) → notifikasi ke pimpinan.

**Belum dikerjakan (Fase 7 — QA & Hardening, belum diminta):** dokumen uji Bab IV, README akun demo, audit aksesibilitas, uji perangkat nyata.

### Fase 5 — Lane E Dashboard & Laporan — 2026-10-03

**Selesai:**
- Dashboard per role (`Services\Dashboard\{Operator,Pimpinan,Admin}Dashboard`, view `dashboard/{role}`):
  - Operator: hero + tombol utama kontekstual (Buat Periode / Lanjutkan Penilaian / Hitung MOORA / Lihat Peringkat) + progres nilai periode aktif, stat, alur kerja, aset perlu perhatian, bobot kriteria, aset per kategori.
  - Pimpinan: "N aset menunggu keputusan" + Mulai Putuskan, bar distribusi rekomendasi bertumpuk (tanpa library chart), 5 aset prioritas (skor terendah belum diputuskan), periode final, laporan terbaru.
  - Admin: stat sistem, pengguna per role, ambang rekomendasi, aktivitas terbaru (terisi setelah audit log Fase 6), daftar periode.
- Laporan: `LaporanGenerator` (PDF A4 dompdf / Excel multi-sheet), `LaporanController` (index + riwayat, store, download), `StoreLaporanRequest` (hanya periode dihitung/final). PDF: kop + logo + alamat instansi, ringkasan, tabel kriteria/matriks/peringkat/keputusan (header berulang tiap halaman), ⚠ biaya tinggi, blok tanda tangan + NIP, nomor halaman. Riwayat di tabel `laporan`, berkas di disk privat.
- Enum `FormatLaporan`, pengaturan `alamat_instansi` (seeder diperbarui — jalankan `php artisan db:seed --class=PengaturanSeeder` pada DB yang sudah ada).
- Test `LaporanDashboardTest` (12): PDF & Excel tiap jenis, 96 aset multi-halaman, validasi periode, hak akses, berkas hilang, dashboard 3 role, **jumlah query tidak bertambah seiring jumlah aset (tanpa N+1)**. Total 113 test / 525 asersi hijau.

**Cara mencoba:** setelah periode dihitung → menu Laporan → pilih periode, jenis, format → Buat & Unduh; riwayat dapat diunduh ulang (admin hanya unduh).

### Fase 4 — Lane D Peringkat, Keputusan, Finalisasi — 2026-10-03

**Selesai:**
- `PeringkatController` (index, show, detail perhitungan, menu Hasil MOORA → periode terbaru yang dihitung/final) dan `KeputusanController` (menu Keputusan → daftar belum diputuskan, edit/update keputusan, finalisasi) — route `keputusan.*` & `periode.finalisasi` di grup `role:pimpinan`; peringkat read-only untuk semua role.
- Actions: `SaveKeputusan` (hanya status dihitung; catatan ≥10 karakter bila beda dari rekomendasi), `FinalizePeriode` (semua aset wajib diputuskan; status aset: Pertahankan→aktif, Perbaiki→dalam_perbaikan, Hapus→diusulkan_hapus; notifikasi ke operator & admin).
- `Services\Peringkat\PeringkatData` (query halaman) + `PeringatanBiayaTinggi` (aturan #8: Fungsi ≥4 & Biaya = skala maks).
- View `peringkat/` (index: hero + progres keputusan + stat rekomendasi + filter + kartu/tabel; show: nilai tiap kriteria + Yi/skor/rekomendasi/keputusan + kondisi & foto; detail-perhitungan: tab Matriks · Normalisasi · Terbobot · Hasil, penyebut diturunkan dari hasil tersimpan) dan `keputusan/edit` (3 kartu tindakan, rekomendasi ditandai, catatan wajib dinamis, navigasi aset).
- Komponen baru: `ui/rank`, `ui/skor`, `ui/nilai-kriteria`, `ui/kondisi-foto`. Enum `TindakanAset` + `icon()`, `keterangan()`, `statusAset()`.
- Test `PeringkatKeputusanTest` (12) memakai golden dataset §6 (urutan A3, A1, A4, A2; Yi 0,4170; penyebut 7,348469 & 5,477226). Total 101 test / 474 asersi hijau.

**Cara mencoba:** operator menyelesaikan periode & Hitung MOORA → login pimpinan → Peringkat Aset → Mulai Putuskan → simpan tiap aset → Finalisasi Periode.

### Fase 3 — Lane C Periode & Input Penilaian — 2026-10-02

**Selesai:**
- `PeriodeController` (index, create/store, show, edit/update, hitung, buka-kembali) + `PenilaianController` (edit/update) — route `periode.*`, `penilaian.edit|update` di grup `role:operator`; index/show tetap read-only untuk admin & pimpinan.
- Actions: `CreatePeriode` (cakupan semua / per kategori / perlu perhatian / pilih manual; hanya aset Aktif; minimal 2 aset; hanya satu periode non-final), `SaveNilaiAset` (nilai kosong menghapus nilai lama; foto per periode; deskripsi kondisi di pivot; status otomatis draft ↔ dinilai; bila periode sudah dihitung → hasil & keputusan dihapus), `HitungPeriode` (membungkus `CalculatePeriode` Fase 1 tanpa mengubahnya + notifikasi ke pimpinan & admin), `ReopenPeriode` (alasan wajib min 10 karakter, ditolak bila ada periode aktif lain, notifikasi).
- `Services\Periode\PeriodeDetail` mengumpulkan query halaman (progres, alasan tombol Hitung nonaktif, filter Belum/Lengkap, aset berikutnya yang belum lengkap).
- View: daftar periode, form buat periode (kartu cakupan + pencarian aset manual), detail periode (hero progres, tombol Hitung MOORA / alasan nonaktif, peringatan setelah dihitung, kartu buka kembali saat final, daftar aset kartu/tabel), input nilai per aset (`<x-form.scale-radio>` baru: kartu skala 1–5 + rubrik terpilih, tombol Kosongkan; foto kamera/galeri + pratinjau; Sebelumnya/Berikutnya; sticky Simpan / Simpan & Lanjut; konfirmasi SweetAlert bila periode sudah dihitung).
- `<x-confirm-form>` mendapat prop `when` (konfirmasi kondisional).
- Test: `PeriodePenilaianTest` (14). Total 89 test / 401 asersi hijau.

**Cara mencoba:** operator → Periode Penilaian → Buat Periode → Input Nilai tiap aset (Simpan & Lanjut) → Hitung MOORA (aktif bila bobot 100% & nilai lengkap) → diarahkan ke halaman peringkat (diisi Fase 4).

### Perombakan UI + Notifikasi — 2026-10-02 (sebelum Fase 3)

**Selesai:**
- Pedoman UI baru `docs/05-UI-PATTERNS.md` (wajib untuk semua fase berikutnya) + kontrak komponen diperbarui di `02-ARCHITECTURE.md §5`.
- Identitas visual: petrol (`brand`) + zinc + amber, font Manrope, tema DaisyUI `sikaset`.
- Shell baru: sidebar `brand-950`, topbar (judul/subjudul halaman, lonceng notifikasi, menu pengguna), bottom-nav ponsel 4 item; konten memenuhi lebar (tanpa `max-w`).
- Komponen baru: `hero`, `stat-grid`, `btn`, `table-action`, `badge`, `notification-bell`, `user-menu`; `card` (ikon/chip/tautan/flush), input form bergaya baru, `confirm-form` (merah untuk DELETE).
- Halaman dirombak: login (panel terbagi), dashboard (hero + stat + alur kerja SPK + aset perlu perhatian + bobot kriteria + aset per kategori), Data Aset (index/show/form 2 tahap dengan pratinjau foto/import/pratinjau import), Kategori, Kriteria (index + form rubrik), Profil, Notifikasi; placeholder Fase 3–6 menyesuaikan shell.
- Fitur notifikasi: tabel `notifications`, `SistemNotification`, service `Notifikasi`, `NotifikasiController` (daftar, buka + tandai dibaca, tandai semua). Import BMD mengirim notifikasi ke admin & operator lain.
- Test: 75 test hijau (tambah `NotifikasiTest` dan uji notifikasi import).

**Cara mencoba:** login ketiga akun; cek Beranda, Data Aset, dan Kriteria di desktop & ponsel (DevTools 390 px); lakukan import BMD lalu login sebagai admin → lonceng menampilkan notifikasi.

### Fase 2 — Lane B Master Data (Operator) — 2026-10-02

**Selesai:**
- **Data Aset** (`AsetController`, `views/aset/*`): index dengan pencarian nama/kode, filter kategori & status, paginasi 15 (`withQueryString`), tabel ≥md / kartu <md; detail (data BMD 11 kolom + galeri foto + alert sisa UEB); form tambah/ubah **2 tahap** (Data BMD → Kondisi & Foto, Alpine `step`), foto via kamera (`capture="environment"`) atau galeri (maks 5 foto @4 MB, disk `public`); hapus = soft delete via `<x-confirm-form>`, **ditolak** bila aset ada di periode final (`DeleteAset` → `DomainException`); hapus foto per item; badge ⚠ `<x-ui.badge-perhatian>` bila `sisa_ueb ≤ 3`.
- **Import BMD** (`AsetImportController`, `App\Imports\AsetImport`, `App\Services\Aset\AsetImportFile`, `App\Actions\Aset\ImportAset`): unduh template → unggah (.xlsx/.xls, maks 12 MB, disimpan sementara di disk privat `local/imports/aset-{user}.ext`) → **pratinjau** (total/valid/error + daftar error per baris) → konfirmasi SweetAlert → simpan (upsert `kode_barang`+`nup`, kategori auto-create, transaksi DB). Mendukung angka `1.234.567,89`, tanggal `dd/mm/yyyy`/serial Excel/tahun saja, baris judul di 10 baris teratas, NUP otomatis berurutan per kode barang. 96 baris < 10 detik (diuji).
- **Export** (`aset.export`, `App\Exports\AsetExport`): seluruh aset ke .xlsx; kolom = template import (+Status) sehingga hasil export bisa diimpor ulang tanpa duplikasi (diuji).
- **Kategori Aset** (`KategoriAsetController`, `views/kategori-aset/*`): CRUD; hapus ditolak bila masih dipakai aset (termasuk yang soft-deleted). Menu baru "Kategori Aset" di sidebar operator.
- **Kriteria** (`KriteriaController`, `Actions/Kriteria/*`, `views/kriteria/*`): index + meteran total bobot `<x-ui.progress-meter strict>` (hijau tepat 100%, merah selain itu) + rubrik lipat; CRUD dengan bobot input persen (disimpan desimal 4 digit), urutan, aktif/nonaktif, rubrik skala 1–5 di form; kriteria yang dipakai periode final: badge "Terkunci", tipe & skala dipaksa tetap (`UpdateKriteriaRequest::prepareForValidation`), tombol hapus disembunyikan & ditolak.
- **Hak akses:** semua CUD + import/export/kategori di grup `role:operator`; admin & pimpinan read-only (index/show aset, index kriteria) — tombol aksi disembunyikan dan route mengembalikan 403 (diuji).
- Komponen baru: `ui/badge-perhatian`, `ui/detail-item`, `ui/rubrik-list`; `ui/progress-meter` mendapat prop opsional `strict` (kompatibel mundur). Enum `StatusAset`/`TipeKriteria` mendapat `options()` untuk dropdown.
- Test baru: `AsetTest` (16), `AsetImportTest` (14), `KriteriaTest` (10).

**Status kualitas:** `composer check` hijau — Pint passed, Larastan level 5 0 error, Pest 70 tests / 316 assertions. Aturan Emas: grep `<style|<script` di luar `assets.blade.php` = 0; tidak ada `confirm()/alert()`. `migrate:fresh --seed` (MySQL) OK.

**Cara mencoba:** login `operator`/`password` → menu Data Aset (Tambah / Import BMD / Export), Kategori Aset, Kriteria. Login `pimpinan` atau `admin` → Data Aset & Kriteria tampil tanpa tombol aksi.

**Utang teknis / catatan:**
- Audit log (spatie activitylog) untuk Aset/Kriteria belum dipasang — dijadwalkan bersama Audit Log di Fase 6.
- Laravel Boost **sudah dipasang** (dev) atas izin user: MCP server `laravel-boost` (`.mcp.json`), skills di `.claude/skills`, guideline ditambahkan di AGENTS.md/CLAUDE.md. Jalankan ulang `php artisan boost:update` bila paket diperbarui. Panduan Boost yang menyebut Vite/npm diabaikan (lihat pengantar CLAUDE.md).
- Cek visual manual 360px di browser belum dilakukan oleh agent (hanya struktur kelas mobile-first + smoke test HTTP); mohon dicek sekilas.
- Subtitle `laporan/index.blade.php` (Lane E) masih ter-escape ganda `&amp;amp;` — perbaiki saat Fase 5.
- Commit Git masih ditunda sesuai permintaan user (belum ada branch `phase/2-master-data`).

### Titik Aman Berhenti: Fase 0 + Refactoring Role + Fase 1 MOORA Core Selesai — 2026-10-01

**Kondisi Sistem Saat Ini (Titik Aman):**
1. **Refactoring Role (Sesuai Kesepakatan User):**
   - Role disederhanakan dan disesuaikan menjadi 3 role yang tepat sasaran:
     - **Admin** (`admin` / `password`): Administrator Sistem (kelola pengguna, pengaturan ambang batas, audit log).
     - **Operator** (`operator` / `password`): Pengurus Barang (kelola aset & import BMD, kriteria & bobot, periode penilaian, input nilai, hitung MOORA).
     - **Pimpinan** (`pimpinan` / `password`): Camat / Pimpinan (lihat peringkat & detail, penentuan tindakan aset, finalisasi periode, cetak laporan).
   - Role lama `super_admin` dihapus sepenuhnya dari kode, enum, seeder, form login, route, dan dokumentasi.
   - Halaman login (`/login`) telah diperbarui dengan kartu akun demo interaktif ber-Alpine.js: klik satu tombol (Admin, Operator, atau Pimpinan) langsung mengisi otomatis username dan kata sandi tanpa perlu mengetik manual.

2. **Fase 1 MOORA Core (Selesai Penuh & Terverifikasi):**
   - `app/Services/Moora/MooraInput.php`: DTO input kriteria dan matriks alternatif.
   - `app/Services/Moora/MooraResult.php` & `MooraRow.php`: DTO output hasil perhitungan.
   - `app/Services/Moora/MooraCalculator.php`: Algoritma MOORA murni PHP tanpa dependensi DB (penyebut nol ditangani aman, skor relatif ternormalisasi 0–100, ranking competition).
   - `app/Services/Recommendation/RecommendationResolver.php`: Penentu rekomendasi tindakan (Pertahankan, Perbaiki, Hapus) berdasarkan skor relatif dan ambang batas setting dinamis.
   - `app/Actions/Periode/CalculatePeriode.php`: Orchestrator transaksi database lengkap dengan snapshot kriteria dan snapshot ambang batas, serta validasi integritas (bobot = 1,00, minimal kriteria & aset, penolakan periode final).
   - Seluruh unit test golden dataset dan edge-cases lulus dengan toleransi presisi 1e-4.

3. **Status Kualitas Kode (Composer Check):**
   - **Pest Tests:** 30 tests, 104 assertions — 0 failures (100% green).
   - **Laravel Pint:** 100% PSR-12 compliant (0 lint errors).
   - **Larastan (PHPStan Level 5):** 0 errors (`{"tool":"phpstan","result":"passed","errors":0}`).
   - **Aturan Emas:** 0 custom CSS, 0 script asing di view (`grep -rn "<style\|<script" resources/views | grep -v assets.blade` bernilai 0/bersih).

4. **Cara Mencoba di Browser:**
   - Akses: `http://127.0.0.1:8000/login`
   - Klik salah satu kotak Akun Demo:
     - **Admin** (Administrator Sistem): login `admin`, password `password`.
     - **Operator** (Pengurus Barang): login `operator`, password `password`.
     - **Pimpinan** (Camat Batuceper): login `pimpinan`, password `password`.
   - Klik "Masuk ke Sistem" → masuk ke dashboard sesuai role masing-masing.

5. **Langkah Berikutnya (Untuk Sesi Esok Hari):**
   - Mulai **Fase 2: Master Data (Lane B)**:
     - Pengelolaan Aset (CRUD, foto aset kamera/upload, filter kategori/status).
     - Import Data BMD dari Excel sesuai format Permendagri/data aset Kecamatan Batuceper.
     - Pengelolaan Kategori Aset & Kriteria Penilaian (meter bobot total 100% + rubrik skala 1–5).
