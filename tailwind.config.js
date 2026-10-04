/**
 * Konfigurasi Tailwind CSS v3 untuk SIKASET — dibangun dengan Tailwind *standalone CLI* (tanpa npm/Vite)
 * lewat `php artisan sikaset:css`. Hasilnya (public/css/app.css) di-commit, server produksi tidak perlu build.
 * Isi `theme` identik dengan konfigurasi Tailwind Play CDN sebelumnya, sehingga tampilan tidak berubah.
 */
module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
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
};
