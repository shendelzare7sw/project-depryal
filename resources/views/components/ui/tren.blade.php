{{-- Perubahan peringkat antar periode: naik (hijau), turun (merah), tetap, atau baru/keluar (null). --}}
@props(['naik', 'baru' => false])
@if ($naik === null)
<x-ui.badge :tone="$baru ? 'info' : 'ghost'" {{ $attributes }}>{{ $baru ? 'Baru' : 'Tidak dinilai' }}</x-ui.badge>
@elseif ($naik > 0)
<span {{ $attributes->class('inline-flex shrink-0 items-center gap-1 whitespace-nowrap text-xs font-extrabold text-emerald-700') }}><x-heroicon-m-arrow-trending-up class="h-4 w-4" /> Naik {{ $naik }}</span>
@elseif ($naik < 0)
<span {{ $attributes->class('inline-flex shrink-0 items-center gap-1 whitespace-nowrap text-xs font-extrabold text-rose-700') }}><x-heroicon-m-arrow-trending-down class="h-4 w-4" /> Turun {{ abs($naik) }}</span>
@else
<span {{ $attributes->class('inline-flex shrink-0 items-center gap-1 whitespace-nowrap text-xs font-bold text-zinc-500') }}><x-heroicon-m-minus class="h-4 w-4" /> Tetap</span>
@endif
