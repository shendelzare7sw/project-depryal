# SIKASET — SPK Penilaian Kelayakan Aset BMD (Metode MOORA)

Sistem Pendukung Keputusan (SPK) berbasis web untuk menilai kelayakan Barang Milik Daerah (BMD) gedung dan bangunan di **Kecamatan Batuceper, Kota Tangerang** menggunakan metode **Multi-Objective Optimization on the basis of Ratio Analysis (MOORA)**.

---

## Ringkasan Akun Demo (Lokal)

| Role | Username | Password | Deskripsi Tugas |
|---|---|---|---|
| **Admin** | `admin` | `password` | Administrator Sistem: Kelola pengguna, pengaturan ambang batas, audit log |
| **Operator** | `operator` | `password` | Pengurus Barang: Kelola aset, import BMD, kriteria, periode, input nilai, hitung MOORA |
| **Pimpinan** | `pimpinan` | `password` | Camat: Melihat peringkat & detail, penentuan tindakan (keputusan), finalisasi periode |

---

## Status Progres Proyek

Dokumentasi lengkap dan status per fase dicatat secara bertahap di:
- [docs/PROGRESS.md](file:///c:/laragon/www/sikaset/docs/PROGRESS.md): Log kemajuan fase dan catatan serah-terima
- [docs/DECISIONS.md](file:///c:/laragon/www/sikaset/docs/DECISIONS.md): Keputusan arsitektur & teknis
- [docs/01-PRODUCT-SPEC.md](file:///c:/laragon/www/sikaset/docs/01-PRODUCT-SPEC.md): Spesifikasi produk, role, aturan bisnis, dan MOORA
- [docs/02-ARCHITECTURE.md](file:///c:/laragon/www/sikaset/docs/02-ARCHITECTURE.md): Arsitektur sistem, skema basis data, dan rute
- [docs/04-TASKS.md](file:///c:/laragon/www/sikaset/docs/04-TASKS.md): Rencana tahapan kerja (Fase 0 s.d. Fase 7)

### Ringkasan Capaian Saat Ini
- [x] **Fase 0a & 0b (Foundation)**: Migrasi 15 tabel, 11 Model Eloquent, 6 Enums, Seeder lengkap, UI DaisyUI + Tailwind CDN + Alpine.js, 14 Komponen Blade, Auth & middleware role.
- [x] **Refactoring Role**: 3 role final (`admin`, `operator`, `pimpinan`). Halaman login interaktif *click-to-fill*.
- [x] **Fase 1 (MOORA Core)**: `MooraCalculator` (algoritma murni PHP), `RecommendationResolver`, `CalculatePeriode` action, serta unit & feature test 100% lulus.
- [ ] **Fase 2 (Master Data Aset & Kriteria)**: *Antrean berikutnya*.

---

## Panduan Menjalankan Aplikasi

1. **Jalankan Web Server & MySQL** (via Laragon / Nginx / Apache).
2. **Migrasi & Seeder Database:**
   ```bash
   php artisan migrate:fresh --seed
   ```
3. **Jalankan Server Development:**
   ```bash
   php artisan serve --port=8000
   ```
4. **Buka di Browser:**
   Akses `http://127.0.0.1:8000/login`, lalu klik tombol demo (**Admin**, **Operator**, atau **Pimpinan**) untuk masuk.

---

## Pengujian & Kualitas Kode

- **Pest Tests:** `php artisan test` (30 tests, 104 assertions, 0 failures)
- **Formatting (Pint):** `./vendor/bin/pint --test`
- **Analisis Statis (Larastan):** `./vendor/bin/phpstan analyse`
