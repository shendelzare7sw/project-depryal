# AGENTS.md — SIKASET (SPK Penilaian Kelayakan Aset, Metode MOORA)

> File ini dibaca oleh SEMUA agent (Claude Code, Codex, Qwen Code, Gemini CLI, Cursor, dll).
> Untuk tool yang mencari nama file lain (bila memakai agent selain satu), buat symlink: `ln -s AGENTS.md CLAUDE.md && ln -s AGENTS.md QWEN.md && ln -s AGENTS.md GEMINI.md`.
> Baca dokumen ini sampai selesai SEBELUM menulis kode. Dokumen rinci ada di `docs/`.

## 1. Ringkasan Produk
Aplikasi web **Sistem Pendukung Keputusan (SPK)** untuk menilai kelayakan Barang Milik Daerah (BMD, gedung & bangunan) di Kecamatan Batuceper dengan metode **MOORA**. Menghasilkan peringkat aset + rekomendasi tindakan (Pertahankan / Perbaiki / Hapus). Keputusan akhir tetap di tangan Pimpinan (sistem hanya alat bantu).

## 2. Tech Stack (WAJIB, jangan ganti tanpa persetujuan)
| Lapisan | Pilihan |
|---|---|
| Backend | Laravel (rilis stabil terbaru; cek `composer show laravel/framework`), PHP sesuai syarat Laravel tersebut |
| Web server | **Nginx + PHP-FPM** |
| Database | **MySQL 8** (utf8mb4) |
| Frontend | Blade + **Alpine.js** + **Tailwind CSS** + **DaisyUI** (semua via **CDN**, tanpa Vite/npm) |
| Konfirmasi/notifikasi | **SweetAlert2** (CDN) |
| Ikon | `blade-ui-kit/blade-heroicons` |
| PDF / Excel | `barryvdh/laravel-dompdf`, `maatwebsite/excel` |
| Audit log | `spatie/laravel-activitylog` |
| Test & kualitas | Pest, Laravel Pint, Larastan |

Pin versi CDN (jangan `@latest`) dan kumpulkan di SATU file: `resources/views/layouts/partials/assets.blade.php`.

## 3. Aturan Emas (non-negotiable)
1. **Tidak ada file CSS/JS buatan sendiri.** Tidak ada `public/css/*.css` atau `public/js/*.js` custom, tidak ada `<style>` / `<script>` blok panjang di view. Semua tampilan = kelas utility Tailwind/DaisyUI. Semua interaksi = atribut Alpine inline (`x-data`, `@click`, `x-show`) yang pendek (maks ±3 baris).
2. **Pengulangan UI → Blade component.** Jika pola markup muncul ≥2 kali, jadikan komponen di `resources/views/components/`. View halaman hanya merakit komponen.
3. **Semua dialog konfirmasi & notifikasi pakai SweetAlert2** lewat 2 komponen saja: `<x-confirm-form>` (logout, hapus, hitung ulang, finalisasi, dll.) dan `<x-flash>` (toast dari session). Dilarang `confirm()`, `alert()`, modal DaisyUI untuk konfirmasi.
4. **Controller tipis** (maks ±7 baris per method): validasi → `FormRequest`, logika bisnis → `Service`/`Action`, otorisasi → `Policy`/middleware. Dilarang query kompleks atau perhitungan di controller/view.
5. **Mobile-first.** Tulis kelas tanpa prefix untuk layar 360px dulu, lalu `md:` / `lg:` untuk layar lebih besar. Tabel ≥`md`, kartu (card list) <`md`. Target sentuh min 44px.
6. **Bahasa:** istilah domain mengikuti laporan (Indonesia: `Aset`, `Kriteria`, `Keputusan`…); istilah teknis/kode (class, method, variabel generik) bahasa Inggris. UI & pesan validasi: Bahasa Indonesia (`APP_LOCALE=id`).
7. **Tidak ada magic string.** Role, tipe kriteria, tindakan, status → PHP **Enum** di `app/Enums`.
8. **Mass assignment aman**, semua input divalidasi `FormRequest`, semua query dari input pakai Eloquent/binding. Upload file divalidasi: foto `image|max:4096` (4 MB); dokumen (Excel, PDF, dan lainnya) `file|max:12288` (12 MB).
9. **Logika MOORA hanya di satu tempat:** `app/Services/Moora/MooraCalculator.php` (pure PHP, tanpa akses DB) — agar mudah di-unit-test dan dibandingkan dengan perhitungan manual (kebutuhan Bab IV laporan).
10. **Jangan menambah package di luar daftar** tanpa menuliskan alasan di PR/commit.

## 4. Konvensi Kode
- PSR-12 via `./vendor/bin/pint`; static analysis `./vendor/bin/phpstan analyse` (level 5+).
- `declare(strict_types=1);` di semua file PHP aplikasi baru.
- Model: set `protected $table` eksplisit (nama tabel Indonesia tanpa plural: `aset`, `kriteria`).
- Route: resource controller + nama route `modul.aksi` (mis. `aset.index`). Grup route per role dengan middleware `role:`.
- Query berulang → `scope` di Model. Format angka/rupiah → helper di Model accessor/komponen `<x-rupiah>`, bukan di view.
- Commit kecil, pesan Conventional Commits (`feat(aset): import excel BMD`).

## 5. Definition of Done (setiap task)
- [ ] Fitur sesuai acceptance criteria di `docs/04-TASKS.md`
- [ ] Mobile 360px tidak overflow horizontal (selain tabel di dalam `overflow-x-auto`)
- [ ] Pint + Larastan lolos; Pest hijau (`php artisan test`)
- [ ] Tidak melanggar Aturan Emas (cek: `grep -rn "<style\|<script" resources/views | grep -v assets.blade` hanya boleh muncul di `assets.blade.php`/komponen resmi)
- [ ] Migrasi + seeder dapat dijalankan ulang: `php artisan migrate:fresh --seed`

## 6. Protokol Kerja Bertahap (Satu Agent)
- Kerjakan **satu fase per sesi** sesuai `docs/04-TASKS.md` (Fase 0 → 7, berurutan). Jangan mengerjakan fase berikutnya sebelum diminta.
- Awal sesi: baca `docs/PROGRESS.md` untuk tahu posisi terakhir. Akhir sesi: perbarui `docs/PROGRESS.md` dan `docs/DECISIONS.md`.
- Satu fase = satu branch `phase/<n>-<nama>`; merge ke `main` setelah acceptance lulus dan `composer check` hijau.
- Kontrak (nama Enum, kolom, signature Service) ada di `docs/02-ARCHITECTURE.md`. Jika perlu berubah, perbarui dokumen itu di commit yang sama.
- Jika spesifikasi ambigu: pilih opsi paling sederhana yang konsisten dengan dokumen, tulis asumsi di `docs/DECISIONS.md` (append only).
- Penanda `// [LANE-X]` di `routes/web.php` tetap dipakai sebagai tempat menambah route fase terkait.

## 7. Peta Dokumen
| File | Isi |
|---|---|
| `docs/00-LAPORAN-MATCHING.md` | Pencocokan laporan ↔ sistem, celah & perbaikan |
| `docs/01-PRODUCT-SPEC.md` | Role, izin, aturan bisnis, MOORA, alur UX, inventaris halaman |
| `docs/02-ARCHITECTURE.md` | Struktur folder MVC, skema DB, route, kontrak Service, komponen UI |
| `docs/03-SETUP.md` | Instalasi dari nol (composer, nginx, mysql), `.env`, CDN |
| `docs/04-TASKS.md` | Rencana fase berurutan (satu agent) + prompt siap tempel |
| `docs/PROGRESS.md` | Status fase & catatan serah-terima antar sesi (dibuat Fase 0) |
