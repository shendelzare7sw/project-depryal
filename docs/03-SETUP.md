# 03 — Setup dari Nol (Laravel + Nginx + MySQL, CDN-only frontend)

> Dikerjakan oleh **Lane 0 (Foundation)**. Asumsi: Linux/WSL2 dengan PHP-FPM, Nginx, MySQL 8, Composer, Git. Tidak perlu Node/npm.
> Jalankan perintah satu per satu; jika ada yang gagal, perbaiki dulu sebelum lanjut.

## 1. Prasyarat (verifikasi)
```bash
php -v                      # sesuai syarat Laravel terbaru (cek https://laravel.com/docs → Installation)
php -m | grep -Ei "pdo_mysql|mbstring|xml|ctype|json|fileinfo|gd|zip|bcmath|intl|tokenizer"
composer -V
mysql --version             # 8.x
nginx -v
git --version
```
Ekstensi PHP wajib: `pdo_mysql mbstring xml ctype fileinfo gd zip bcmath intl`. (`gd`/`zip` dibutuhkan dompdf & maatwebsite/excel.)

## 2. Buat Proyek
```bash
composer create-project laravel/laravel sikaset
cd sikaset
git init && git add -A && git commit -m "chore: laravel skeleton"
```
Salin dokumen ke repo: `AGENTS.md` (root) dan folder `docs/`. Buat symlink untuk agent lain:
```bash
ln -s AGENTS.md CLAUDE.md; ln -s AGENTS.md QWEN.md; ln -s AGENTS.md GEMINI.md
touch docs/DECISIONS.md
```

## 3. Database MySQL
```sql
CREATE DATABASE sikaset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sikaset'@'localhost' IDENTIFIED BY 'ganti_password_kuat';
GRANT ALL PRIVILEGES ON sikaset.* TO 'sikaset'@'localhost';
FLUSH PRIVILEGES;
```
(Buat DB kedua `sikaset_test` untuk Pest, atau pakai SQLite in-memory pada `phpunit.xml` untuk test Unit.)

## 4. Konfigurasi `.env`
```dotenv
APP_NAME=SIKASET
APP_ENV=local
APP_DEBUG=true
APP_URL=http://sikaset.test
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta        # tambahkan juga di config/app.php: 'timezone' => env('APP_TIMEZONE', 'UTC')

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikaset
DB_USERNAME=sikaset
DB_PASSWORD=ganti_password_kuat

SESSION_DRIVER=database          # tetap bawaan Laravel (tabel sessions sudah ada di migrasi default)
CACHE_STORE=database
QUEUE_CONNECTION=sync            # sederhana; cukup untuk skala ini
FILESYSTEM_DISK=public
```
Lalu: `php artisan key:generate && php artisan storage:link`.

## 5. Install Package
```bash
# runtime
composer require barryvdh/laravel-dompdf maatwebsite/excel spatie/laravel-activitylog blade-ui-kit/blade-heroicons
# dev
composer require --dev pestphp/pest pestphp/pest-plugin-laravel larastan/larastan laravel/pint barryvdh/laravel-debugbar
./vendor/bin/pest --init

# publish konfigurasi yang diperlukan
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```
> Jika ada package yang belum kompatibel dengan versi Laravel terbaru, **jangan downgrade Laravel**; catat di `docs/DECISIONS.md` dan pakai versi package terdekat yang kompatibel / alternatif sederhana.

Buat `phpstan.neon`:
```neon
includes:
    - vendor/larastan/larastan/extension.neon
parameters:
    paths: [app]
    level: 5
```
Skrip bantu di `composer.json` → `"scripts": { "check": ["pint --test", "phpstan analyse --memory-limit=1G", "@php artisan test"] }`.

## 6. Nginx (server block)
`/etc/nginx/sites-available/sikaset` (ubah path & versi PHP-FPM):
```nginx
server {
    listen 80;
    server_name sikaset.test;
    root /var/www/sikaset/public;
    index index.php;
    charset utf-8;
    client_max_body_size 10M;                 # unggah foto & Excel

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.x-fpm.sock;   # sesuaikan versi
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ /\.(?!well-known).* { deny all; }
}
```
```bash
sudo ln -s /etc/nginx/sites-available/sikaset /etc/nginx/sites-enabled/ && sudo nginx -t && sudo systemctl reload nginx
echo "127.0.0.1 sikaset.test" | sudo tee -a /etc/hosts
sudo chown -R $USER:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache
```
PHP: set `upload_max_filesize=10M`, `post_max_size=12M` di `php.ini` FPM.

## 7. Lokalisasi Indonesia
- `config/app.php`: locale `id`.
- Terjemahan validasi: salin `lang/id/validation.php` (paket komunitas `laravel-lang/lang`: `composer require --dev laravel-lang/common` lalu `php artisan lang:add id`), atau tulis manual. Atribut field Indonesia di `lang/id/validation.php` → `attributes`.

## 8. Urutan Verifikasi Fondasi (Lane 0 harus lulus semua)
1. `php artisan migrate:fresh --seed` sukses.
2. Buka `http://sikaset.test/login` → halaman login tampil rapi di lebar 360px.
3. Login `admin` / `password` → dashboard kosong tampil, drawer & bottom-nav jalan.
4. Tombol Logout memunculkan SweetAlert konfirmasi; setelah "Ya" kembali ke login.
5. `grep -rn "<style\|<script" resources/views` → hanya `assets.blade.php` dan komponen `confirm-form`/`flash` (atribut Alpine inline saja).
6. `composer check` hijau.

## 9. Akun Seeder (hanya lokal/demo; ganti di produksi)
| username | password | role | siapa |
|---|---|---|---|
| `admin` | `password` | admin | Administrator Sistem |
| `operator` | `password` | operator | Pengurus Barang |
| `pimpinan` | `password` | pimpinan | Camat Batuceper |

`DemoAsetSeeder` membuat ±20 aset **dummy** (Faker, jangan pakai data BMD asli) + 3 kriteria default (bobot 0,40/0,35/0,25; benefit/benefit/cost; skala 1–5 + rubrik) + 6 kategori (Tugu/Tanda Batas, Bangunan Lainnya, Posyandu, Pagar Permanen, Gedung Kantor, Gedung) + pengaturan ambang.

## 10. Deploy ringkas (nanti)
`APP_ENV=production APP_DEBUG=false` → `composer install --no-dev -o` → `php artisan migrate --force && php artisan config:cache route:cache view:cache` → ganti password seeder → backup MySQL terjadwal (`mysqldump` cron).
