{{-- CDN Assets — SIKASET (satu-satunya tempat aset eksternal & token tema) --}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Font: Manrope --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

{{-- DaisyUI v4.12.24 --}}
<link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.24/dist/full.min.css" rel="stylesheet" type="text/css">

{{-- Tailwind CSS v3 Play CDN --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: { sans: ['Manrope', 'system-ui', 'sans-serif'] },
                colors: {
                    brand: {
                        50: '#effaf8', 100: '#d7f1ec', 200: '#b1e2da', 300: '#7fcbc0', 400: '#4caea2',
                        500: '#2f9286', 600: '#23766d', 700: '#1f5f59', 800: '#1d4d49', 900: '#1b403d', 950: '#0b2523',
                    },
                },
            },
        },
    }
</script>

{{--
    Token tema DaisyUI "sikaset" (petrol + zinc + aksen amber). Hanya variabel warna/radius —
    DaisyUI CDN tidak bisa diberi tema custom tanpa variabel CSS. Tidak ada selector komponen custom.
--}}
<style>
    [data-theme=sikaset] {
        color-scheme: light;
        --p: 44.8% 0.066 186; --pc: 98.5% 0.01 186;
        --s: 76.9% 0.165 70; --sc: 27% 0.06 60;
        --a: 64% 0.1 186; --ac: 98% 0.01 186;
        --n: 26% 0.03 190; --nc: 97% 0.005 190;
        --b1: 100% 0 0; --b2: 97.6% 0.003 190; --b3: 92.4% 0.006 190; --bc: 27.4% 0.012 220;
        --in: 58.8% 0.158 242; --inc: 98% 0.01 242;
        --su: 59.6% 0.127 163; --suc: 98% 0.02 163;
        --wa: 76.9% 0.165 70; --wac: 27% 0.06 60;
        --er: 58.6% 0.222 18; --erc: 98% 0.01 18;
        --rounded-box: 1rem; --rounded-btn: 0.7rem; --rounded-badge: 9999px;
        --animation-btn: 0.18s; --animation-input: 0.18s; --btn-focus-scale: 0.98; --border-btn: 1px;
    }
    [x-cloak] { display: none !important; }
</style>

{{-- Alpine.js v3 --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>

{{-- Cloudflare Turnstile (hanya di halaman login & bila dikonfigurasi) --}}
@if (request()->routeIs('login') && config('services.turnstile.site_key'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

{{-- SweetAlert2 v11 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.2/dist/sweetalert2.all.min.js"></script>
