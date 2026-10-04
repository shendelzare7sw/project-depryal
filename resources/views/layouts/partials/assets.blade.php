{{--
    Aset tampilan SIKASET — satu-satunya tempat memuat CSS/JS. Semua di-host sendiri (tidak bergantung CDN/internet):
    CSS dibangun `php artisan sikaset:css` (Tailwind standalone CLI + DaisyUI ramping), JS vendor versi terkunci.
    Satu-satunya skrip pihak ketiga: Cloudflare Turnstile di halaman login (bila kunci diisi di .env).
--}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Font Manrope ada di dalam app.css (public/fonts/manrope, SIL OFL) --}}
<link rel="preload" href="{{ asset('fonts/manrope/manrope-latin.woff2') }}" as="font" type="font/woff2" crossorigin>

{{-- DaisyUI v4.12.24 (styled + kelas terpakai) lalu Tailwind CSS v3.4.17 + token tema "sikaset" --}}
<link href="{{ asset('vendor/daisyui/daisyui.min.css') }}?v={{ filemtime(public_path('vendor/daisyui/daisyui.min.css')) }}" rel="stylesheet">
<link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">

{{-- Pilihan "Perbesar teks" diterapkan sebelum halaman tampil (diingat per perangkat). --}}
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">try { if (localStorage.getItem('sikaset-teks') === 'besar') document.documentElement.classList.add('teks-besar'); } catch (e) {}</script>

{{-- Alpine.js v3.14.9 --}}
<script defer src="{{ asset('vendor/alpinejs/cdn.min.js') }}"></script>

{{-- Cloudflare Turnstile (hanya di halaman login & bila dikonfigurasi) --}}
@if (request()->routeIs('login') && config('services.turnstile.site_key'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

{{-- SweetAlert2 v11.22.2 --}}
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
