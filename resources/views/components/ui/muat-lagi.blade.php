{{--
    Footer daftar pengganti pagination: "Menampilkan X dari Y" + tombol "Tampilkan N lagi".
    Setelah dimuat, layar langsung menuju data pertama yang baru: item daftar memakai id m-{no} (kartu ponsel)
    dan d-{no} (baris tabel desktop); satu-daftar = daftar tunggal untuk semua layar (id m-{no}).
    Tanpa JavaScript tautan tetap berfungsi (hanya tanpa lompatan posisi).
--}}
@props(['items', 'langkah' => \App\Support\Tampil::LANGKAH, 'satuDaftar' => false])
@php
    $ditampilkan = $items->count();
    $sisa = $items->total() - $ditampilkan;
    $url = request()->fullUrlWithQuery(['tampil' => $ditampilkan + $langkah]);
    $mentok = $ditampilkan >= \App\Support\Tampil::MAKS;
@endphp
@if ($items->total() > 0)
<footer id="muat-lagi" class="flex flex-col items-stretch gap-3 border-t border-zinc-200/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
    <p class="text-center text-xs text-zinc-500 sm:text-left">Menampilkan <strong class="text-zinc-800">{{ $ditampilkan }}</strong> dari <strong class="text-zinc-800">{{ $items->total() }}</strong></p>
    @if ($sisa > 0 && ! $mentok)
    <a href="{{ $url }}" x-data="{ busy: false }" :class="busy && 'pointer-events-none opacity-60'"
        @click.prevent="busy = true; location.href = @js($url) + '#' + ({{ $satuDaftar ? 'true' : 'false' }} || innerWidth < 1024 ? 'm' : 'd') + '-{{ $ditampilkan + 1 }}'"
        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-brand-50 px-4 text-sm font-bold text-brand-700 transition hover:bg-brand-100">
        <span class="loading loading-spinner loading-xs" x-show="busy" x-cloak></span>
        <x-heroicon-o-chevron-double-down class="h-4 w-4" x-show="!busy" />
        Tampilkan {{ min($langkah, $sisa) }} lagi
    </a>
    @elseif ($sisa > 0)
    <p class="text-center text-xs font-semibold text-amber-700">Gunakan pencarian/filter untuk mempersempit daftar.</p>
    @elseif ($items->total() > $langkah)
    <p class="text-center text-xs font-semibold text-emerald-700">Semua data sudah ditampilkan.</p>
    @endif
</footer>
@endif
