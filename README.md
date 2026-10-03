# SIKASET — SPK Penilaian Kelayakan Aset BMD (Metode MOORA)

Sistem Pendukung Keputusan berbasis web untuk menilai kelayakan Barang Milik Daerah (BMD) gedung & bangunan di **Kecamatan Batuceper, Kota Tangerang** dengan metode **MOORA** (*Multi-Objective Optimization on the basis of Ratio Analysis*). Sistem menghasilkan peringkat aset dan rekomendasi tindakan (**Pertahankan / Perbaiki / Hapus**); keputusan akhir tetap di tangan Pimpinan.

## Akun awal (dari seeder — segera ganti kata sandinya)

| Peran | Username | Password | Tugas |
|---|---|---|---|
| Admin | `admin` | `password` | Pengguna, pengaturan ambang & instansi, audit log |
| Operator | `operator` | `password` | Aset & import BMD, kriteria, periode, input nilai, hitung MOORA, laporan |
| Pimpinan | `pimpinan` | `password` | Peringkat, keputusan tindakan, finalisasi, laporan |

Cara kerja lengkap per peran: **[docs/07-ALUR-APLIKASI.md](docs/07-ALUR-APLIKASI.md)**.

## Teknologi
Laravel 13 (PHP 8.3) · MySQL 8 · Blade + Tailwind CSS + DaisyUI + Alpine.js + SweetAlert2 (CDN, tanpa build) · dompdf · Laravel Excel · spatie/activitylog · Pest, Pint, Larastan.

## Menjalankan (lokal)
```bash
composer install
cp .env.example .env && php artisan key:generate   # atur DB_* untuk MySQL
php artisan migrate --seed                          # atau migrate:fresh --seed untuk data awal bersih
php artisan storage:link
php artisan serve                                   # buka http://127.0.0.1:8000/login
```

## Kualitas & pengujian
```bash
composer check   # Pint + Larastan level 5 + Pest (134 test)
```
Dokumen uji black-box & perbandingan perhitungan manual (bahan Bab IV): [docs/06-PENGUJIAN-BAB-IV.md](docs/06-PENGUJIAN-BAB-IV.md).

## Checklist produksi
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` sesuai domain (HTTPS).
- **Cloudflare Turnstile:** buat widget di dash.cloudflare.com → Turnstile (domain aplikasi), lalu isi `TURNSTILE_SITE_KEY` & `TURNSTILE_SECRET_KEY` (jangan pakai test key). Kosongkan keduanya untuk menonaktifkan.
- Ganti kata sandi akun awal (`admin`, `operator`, `pimpinan`) atau buat akun baru lalu nonaktifkan akun awal.
- `SESSION_SECURE_COOKIE=true`, pertimbangkan `SESSION_ENCRYPT=true`.
- `composer install --no-dev --optimize-autoloader` (debugbar tidak ikut), lalu `php artisan config:cache route:cache view:cache`.
- `php artisan storage:link`; pastikan `storage/` & `bootstrap/cache` dapat ditulis web server.
- Cadangkan basis data & folder `storage/app` (foto aset, berkas import, laporan) secara berkala.
- Tailwind memakai Play CDN (cocok untuk pengembangan); untuk produksi resmi disarankan build — semua aset CDN terpusat di `resources/views/layouts/partials/assets.blade.php`.

## Dokumentasi
| Dokumen | Isi |
|---|---|
| [docs/01-PRODUCT-SPEC.md](docs/01-PRODUCT-SPEC.md) | Peran, aturan bisnis, algoritma MOORA |
| [docs/02-ARCHITECTURE.md](docs/02-ARCHITECTURE.md) | Struktur kode, skema DB, kontrak komponen |
| [docs/05-UI-PATTERNS.md](docs/05-UI-PATTERNS.md) | Pola UI wajib |
| [docs/06-PENGUJIAN-BAB-IV.md](docs/06-PENGUJIAN-BAB-IV.md) | Kasus uji & hasil |
| [docs/07-ALUR-APLIKASI.md](docs/07-ALUR-APLIKASI.md) | Alur kerja per peran |
| [docs/PROGRESS.md](docs/PROGRESS.md) · [docs/DECISIONS.md](docs/DECISIONS.md) | Progres fase & keputusan teknis |
