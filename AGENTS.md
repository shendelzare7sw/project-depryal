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
| `docs/05-UI-PATTERNS.md` | **Pola UI wajib** (shell, stat-grid, daftar kartu/tabel, form, notifikasi, gerbang screenshot 390/1440) |
| `docs/PROGRESS.md` | Status fase & catatan serah-terima antar sesi (dibuat Fase 0) |

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
