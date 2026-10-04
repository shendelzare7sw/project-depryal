{{-- Tombol kirim halaman autentikasi; butuh x-data berisi `busy` pada induknya. --}}
<button type="submit" :disabled="busy" class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-700 text-sm font-bold text-white shadow-lg shadow-brand-900/20 transition hover:bg-brand-800 disabled:opacity-60">
    <span class="loading loading-spinner loading-sm" x-show="busy" x-cloak></span>
    {{ $slot }}
    <x-heroicon-m-arrow-right class="h-4 w-4" x-show="!busy" />
</button>
