# 04 — Rencana Kerja Bertahap (Satu Agent) + Prompt Siap Tempel

## 1. Mode Kerja: Satu Agent, Bertahap
Dokumen ini awalnya dirancang untuk banyak agent paralel (istilah "Lane"). Untuk **satu agent**, setiap Lane dikerjakan **berurutan sebagai Fase**. Isi tugas, kepemilikan file, dan acceptance tiap Lane tetap berlaku; yang berubah hanya urutan dan cara serah-terimanya.

| Fase | = Lane | Fokus | Alasan urutan |
|---|---|---|---|
| 0 | Lane 0 | Foundation (setup, migrasi, model, layout, komponen, auth) | Semua fase lain bergantung padanya |
| 1 | Lane A | MOORA Core (Calculator, Resolver) + unit test golden dataset | Logika murni, paling mudah diverifikasi, jantung sistem |
| 2 | Lane B | Master Data: Aset (+import), Kategori, Kriteria | Data harus ada sebelum bisa dinilai |
| 3 | Lane C | Periode & Input Penilaian + `CalculatePeriode` | Alur utama Operator |
| 4 | Lane D | Peringkat, Detail Perhitungan, Keputusan, Finalisasi | Alur utama Pimpinan |
| 5 | Lane E | Dashboard 3 role + Laporan PDF/Excel | Membutuhkan data hasil dari fase 3–4 |
| 6 | Lane F | Admin: Pengguna, Pengaturan, Audit Log, Profil | Pendukung; seeder sudah cukup untuk fase sebelumnya |
| 7 | Lane Z | QA & Hardening + dokumen uji Bab IV | Penutup |

### Aturan kerja bertahap
1. **Satu fase = satu sesi/konteks baru** (mulai chat/sesi baru tiap fase). Agent tidak mengandalkan ingatan; ia membaca `AGENTS.md`, spec yang relevan, dan **`docs/PROGRESS.md`**.
2. **Satu fase = satu branch** (`phase/<n>-<nama>`), merge ke `main` setelah acceptance lulus dan `composer check` hijau. Kamu review singkat (jalankan aplikasi, coba alurnya) sebelum lanjut.
3. Di akhir fase, agent **wajib memperbarui `docs/PROGRESS.md`** (apa yang selesai, cara mencoba, asumsi, utang teknis) dan `docs/DECISIONS.md`.
4. **Jangan mengerjakan fase berikutnya** sebelum diminta, walaupun terlihat mudah.
5. Jika konteks agent mulai penuh di tengah fase, hentikan di titik yang konsisten, tulis status ke `PROGRESS.md`, lalu lanjutkan di sesi baru. Tiap fase sengaja dibuat kecil agar jarang terjadi.
6. Fase 0 sebaiknya dibagi dua commit besar: (a) setup + migrasi + model + seeder, (b) layout + komponen + auth. Boleh dua sesi.

### Template `docs/PROGRESS.md` (dibuat oleh Fase 0, diperbarui tiap fase)
```markdown
# PROGRESS
## Status
- [x] Fase 0 Foundation — selesai (tanggal)
- [ ] Fase 1 MOORA Core
...
## Catatan serah-terima (terbaru di atas)
### Fase N — judul
- Selesai: ...
- Cara mencoba: ...
- Asumsi / keputusan: ... (juga di DECISIONS.md)
- Utang teknis / belum selesai: ...
```

## 2. Lane 0 — Foundation (WAJIB PERTAMA)
**File yang dikerjakan:** semua file inisial: `composer.json`, `bootstrap/app.php`, `config/*`, migrasi, enum, model, factory, seeder, `resources/views/layouts/**`, `resources/views/components/**`, `auth/**`, `routes/web.php` (kerangka), `tests/Pest.php`.
**Tugas:**
1. Ikuti `03-SETUP.md` §2–§7.
2. Buat semua **migrasi** (§2 arsitektur), **Enum** (§3), **Model** + relasi + cast + scope dasar + `$table`, **Factory**, **Seeder** (§9 setup).
3. `EnsureRole` (alias `role`) & `EnsureUserIsActive` (alias `active`) di `bootstrap/app.php`; handler `DomainException` → redirect back + flash error.
4. Layout `app` (appbar + drawer + bottom-nav + `<x-flash>`), `guest`, partial `assets`, sidebar/bottom-nav per role (menu sesuai `01-PRODUCT-SPEC §5.1`; link ke route yang belum ada ditandai TODO dan disembunyikan dengan `Route::has()`).
5. Semua **komponen UI** di `02-ARCHITECTURE §5` (termasuk `confirm-form`, `flash`).
6. Auth: login username, throttle, logout (SweetAlert), halaman `dashboard` placeholder per role.
7. Pest terpasang + `AuthTest`, `RoleAccessTest` (akses ditolak 403 antar-role).
**Acceptance:** `03-SETUP.md §8` lulus semuanya.

## 3. Lane A — MOORA Core (logika murni)
**File yang dikerjakan:** `app/Services/Moora/**`, `app/Services/Recommendation/**`, `app/Actions/Periode/CalculatePeriode.php`, `tests/Unit/Moora*`, `tests/Unit/Recommendation*`, `tests/Feature/CalculatePeriodeTest.php`.
**Tugas:**
1. `MooraInput`, `MooraResult`, `MooraCalculator` sesuai `01-PRODUCT-SPEC §4` (tanpa akses DB/Facade).
2. `RecommendationResolver` memakai `Setting` (ambang) — injeksi lewat constructor agar mudah di-test.
3. `CalculatePeriode` (transaksi DB): validasi bobot aktif = 1,00 (±0,0001), ≥2 kriteria aktif, ≥2 aset, nilai lengkap → bangun matriks → hitung → simpan `hasil_moora` (+`detail` JSON) → isi `snapshot_kriteria` & `snapshot_ambang` → status `dihitung`, `dihitung_pada/oleh`. Hitung ulang: hapus `hasil_moora` & `keputusan` lama periode itu.
4. Tolak (DomainException berpesan Indonesia) jika periode `final` atau data tidak valid.
**Acceptance:** Test golden dataset (`01 §6`) lulus (toleransi 1e-4); test tepi: penyebut nol, 2 alternatif, nilai kembar → ranking kembar, bobot ≠ 1 ditolak, periode final ditolak, hitung ulang menghapus keputusan lama.

## 4. Lane B — Master Data (Operator)
**File yang dikerjakan:** `AsetController`, `AsetImportController`, `KategoriAsetController`, `KriteriaController`, Requests terkait, `app/Imports/AsetImport.php`, `app/Exports/AsetExport.php`, `views/aset/**`, `views/kategori-aset/**`, `views/kriteria/**`, test terkait.
**Tugas:**
1. **Aset:** index (pencarian nama/kode, filter kategori/status, paginate; kartu di mobile), show (data BMD + foto), create/edit (form 2 bagian), hapus (soft delete, ditolak jika ada di periode final), badge ⚠ "perlu perhatian" bila `sisa_ueb ≤ 3`.
2. **Import BMD:** unduh template Excel; unggah → **pratinjau** (jumlah baris valid/error + daftar error per baris) → konfirmasi (SweetAlert) → simpan. Kolom 11 BMD (kode barang, nama, jumlah, luas, tgl perolehan, harga satuan, nilai perolehan, UEB, akumulasi penyusutan, sisa UEB, nilai buku) + kategori (nama; dibuat otomatis bila belum ada) + NUP (opsional). Upsert berdasarkan (`kode_barang`,`nup`). Format angka Indonesia (`1.234.567,89`) dan tanggal `dd/mm/yyyy` harus terbaca.
3. **Export** daftar aset ke Excel.
4. **Kategori aset:** CRUD sederhana.
5. **Kriteria:** index dengan **meter total bobot** (`<x-ui.progress-meter>`), CRUD, input bobot dalam %, rubrik skala (`kriteria_skala`) dikelola di form kriteria (5 baris label/deskripsi), aktif/nonaktif, urutan. Kriteria yang sudah dipakai periode final: tombol hapus disembunyikan; tipe & skala terkunci.
**Acceptance:** semua aksi hanya operator (admin & pimpinan read-only di aset & kriteria); import file contoh 96 baris < 10 detik; mobile 360px rapi; semua hapus lewat `<x-confirm-form>`.

## 5. Lane F — Admin (Administrator Sistem)
**File yang dikerjakan:** `PenggunaController`, `PengaturanController`, `AuditLogController`, `ProfilController`, Requests, `views/pengguna|pengaturan|audit-log|profil/**`, konfigurasi activitylog di Model (trait `LogsActivity` pada Aset, Kriteria, PeriodePenilaian, NilaiKriteriaAset, Keputusan, User).
**Tugas:** CRUD pengguna (username unik, role, aktif/nonaktif, reset password oleh Admin dengan password acak yang ditampilkan sekali), pengaturan (ambang pertahankan > ambang perbaiki, 0–100; data instansi & penandatangan laporan), audit log (filter user/modul/tanggal, paginate), profil (ubah nama & password dengan verifikasi password lama).
**Acceptance:** admin tidak bisa menonaktifkan/menurunkan dirinya sendiri; validasi ambang; test akses per role.

## 6. Lane C — Periode & Penilaian (Admin)
**File yang dikerjakan:** `PeriodeController`, `PenilaianController`, `app/Actions/Periode/{CreatePeriode,FinalizePeriode,ReopenPeriode}.php` (Calculate milik Lane A), `app/Actions/Penilaian/SaveNilaiAset.php`, `PeriodePenilaianPolicy`, `views/periode/**`, `views/penilaian/**`, test terkait.
**Tugas:**
1. Daftar & buat periode (nama, tanggal, pilih aset: semua / per kategori / "perlu perhatian"; hanya 1 periode non-final).
2. Halaman periode: progres nilai (x/y), daftar aset (Belum/Lengkap, filter), tombol **Hitung MOORA** (non-aktif + alasan jika bobot ≠ 100% atau nilai belum lengkap), konfirmasi SweetAlert → panggil `CalculatePeriode` → redirect ke peringkat.
3. Halaman **input nilai per aset**: foto (kamera/galeri, multi), deskripsi kondisi, `<x-form.scale-radio>` per kriteria aktif + rubrik, tombol sticky "Simpan & Lanjut". Status periode otomatis `draft`→`dinilai` saat semua lengkap (dan kembali ke `draft` bila ada yang dikosongkan).
4. Aksi **Buka kembali** periode final (alasan wajib, SweetAlert) → `ReopenPeriode`.
**Acceptance:** alur lengkap Operator `01 §5.2` dapat dijalankan end-to-end dengan data seeder; mengubah nilai setelah dihitung menampilkan peringatan bahwa hasil harus dihitung ulang.

## 7. Lane D — Peringkat & Keputusan (Pimpinan)
**File yang dikerjakan:** `PeringkatController`, `KeputusanController`, `SaveKeputusan`, `KeputusanPolicy`, `views/peringkat/**`, `views/keputusan/**`, test terkait.
**Tugas:**
1. **Peringkat:** daftar urut ranking (tabel `md:` / kartu mobile), badge rekomendasi & keputusan, filter rekomendasi/keputusan, ringkasan atas (jumlah per rekomendasi).
2. **Detail aset:** foto, kondisi, nilai tiap kriteria (label rubrik), Yi & skor relatif, badge ⚠ (aturan #8), rekomendasi sistem.
3. **Detail Perhitungan:** tab Matriks · Normalisasi · Terbobot · Hasil dari `hasil_moora.detail` + `snapshot_kriteria` (tampil bobot saat dihitung).
4. **Keputusan:** 3 tombol besar (rekomendasi ditandai), catatan wajib bila beda dari rekomendasi, simpan → toast → ke aset berikutnya yang belum diputuskan.
5. **Finalisasi** (hanya Pimpinan; aktif jika semua aset diputuskan; SweetAlert) → `FinalizePeriode`: kunci, `aset.status` (Pertahankan→aktif, Perbaiki→dalam_perbaikan, Hapus→diusulkan_hapus).
**Acceptance:** Operator & Admin hanya read-only; semua aturan `01 §3` #5–#8 teruji.

## 8. Lane E — Dashboard & Laporan
**File yang dikerjakan:** `DashboardController` + `app/Services/Dashboard/**`, `views/dashboard/**`, `LaporanController`, `LaporanGenerator`, `app/Exports/PeringkatExport.php`, `views/laporan/**`, test terkait.
**Tugas:** dashboard 3 role (`01 §5.2–5.4`, tanpa library chart); laporan PDF (dompdf; kop instansi dari `pengaturan`, tabel peringkat, keputusan, blok tanda tangan penandatangan) & Excel; riwayat cetak di tabel `laporan` (unduh ulang dari storage); jenis: Peringkat / Keputusan / Lengkap.
**Acceptance:** PDF A4 portrait rapi dengan 96 baris (header tabel berulang tiap halaman); dashboard cepat (<300 ms query, tanpa N+1 — cek dengan debugbar).

## 9. Lane Z — QA & Hardening
Feature test alur penuh (`PeriodeFlowTest`: import → nilai → hitung → keputusan → finalisasi → laporan), audit mobile 360/390/768/1280, sapu Aturan Emas (grep), pagination & N+1, aksesibilitas dasar (label form, kontras badge), seed ulang bersih, perbarui README (cara jalan + akun demo). Hasilkan juga **dokumen uji untuk Bab IV**: tabel test case black-box (ID, skenario, input, hasil diharapkan, hasil aktual) dari Feature test dan perbandingan golden dataset vs perhitungan manual.

---
## 10. Prompt Siap Tempel (Satu Agent)
Cocok untuk Claude Code, Codex, Qwen Code, Gemini CLI, dll. Mulai **sesi baru** untuk tiap fase.

### 10.1 Prompt Fase 0
```
Kamu adalah engineer Laravel senior. Proyek: SIKASET (SPK penilaian kelayakan aset, metode MOORA).
1) Baca penuh: AGENTS.md, docs/00-LAPORAN-MATCHING.md, docs/01-PRODUCT-SPEC.md, docs/02-ARCHITECTURE.md, docs/03-SETUP.md, docs/04-TASKS.md.
2) Kerjakan HANYA "Fase 0 = Lane 0 — Foundation" (docs/04-TASKS.md §2), ikuti docs/03-SETUP.md berurutan.
   Proyek Laravel sudah dibuat dengan composer create-project (jangan buat ulang); jalankan composer install bila vendor belum ada.
3) Patuhi "Aturan Emas" di AGENTS.md (tanpa CSS/JS custom, UI via komponen Blade + Tailwind/DaisyUI/Alpine CDN, konfirmasi via SweetAlert, controller tipis, mobile-first).
4) Buat docs/PROGRESS.md (template di docs/04-TASKS.md §1). Jalankan verifikasi docs/03-SETUP.md §8 dan tampilkan hasilnya.
5) Branch phase/0-foundation, commit kecil (Conventional Commits). Asumsi tulis di docs/DECISIONS.md; jangan berhenti bertanya kecuali benar-benar buntu.
6) Jangan mulai Fase 1. Akhiri dengan ringkasan: apa yang dibuat, cara mencoba, hal yang belum selesai.
```

### 10.2 Prompt Fase 1–7 (template)
```
Kamu adalah engineer Laravel senior pada proyek SIKASET. Fase sebelumnya sudah ada di branch main.
1) Baca: AGENTS.md, docs/PROGRESS.md (status & catatan serah-terima), docs/01-PRODUCT-SPEC.md, docs/02-ARCHITECTURE.md, lalu bagian "<NAMA LANE>" di docs/04-TASKS.md.
2) Buat branch phase/<n>-<nama> dari main. Kerjakan HANYA Fase <n> (= <NAMA LANE>). Jangan mengerjakan fase lain.
3) Patuhi kontrak nama Enum/kolom/Service di docs/02-ARCHITECTURE.md dan Aturan Emas di AGENTS.md. Jangan tambah package baru tanpa alasan tertulis di docs/DECISIONS.md.
4) Tulis test Pest untuk setiap acceptance criteria; jalankan `composer check` sampai hijau.
5) Cek manual di lebar 360px: tidak ada overflow horizontal; tidak ada <style>/<script>/file CSS-JS custom di luar yang diizinkan.
6) Perbarui docs/PROGRESS.md (centang fase, catatan serah-terima) dan docs/DECISIONS.md. Commit Conventional Commits.
7) Akhiri dengan ringkasan: file dibuat/diubah, cara mencoba, asumsi, yang belum selesai. Berhenti di sini.
```
Contoh isian: Fase 1 → `<NAMA LANE>` = `Lane A — MOORA Core`; Fase 2 → `Lane B — Master Data (Operator)`; Fase 3 → `Lane C — Periode & Penilaian (Operator)`; Fase 4 → `Lane D — Peringkat & Keputusan (Pimpinan)`; Fase 5 → `Lane E — Dashboard & Laporan`; Fase 6 → `Lane F — Admin (Administrator Sistem)`; Fase 7 → `Lane Z — QA & Hardening`.

### 10.3 Prompt Review Mandiri (opsional, sebelum merge tiap fase; sesi baru)
```
Review branch phase/<n>-... terhadap AGENTS.md (Aturan Emas, Definition of Done) dan docs/02-ARCHITECTURE.md.
Cari: CSS/JS custom, controller gemuk, magic string, query di view, N+1, celah otorisasi, validasi hilang, inkonsistensi dengan docs.
Beri daftar temuan berprioritas (blocker/major/minor) dengan file:baris dan saran. Jangan ubah kode kecuali diminta.
```

## 11. Backlog Fase 2 (jangan dikerjakan sekarang)
Role `auditor` (read-only BPKD) · pelacakan usulan penghapusan ke BPKD (nomor surat, SK Walikota) · perbandingan peringkat antar periode · notifikasi email/WhatsApp ke Pimpinan · build aset produksi (Vite) menggantikan CDN Tailwind · backup otomatis dari aplikasi.
