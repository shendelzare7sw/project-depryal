{{--
    Tombol standar h-10 rounded-xl. tone: primary|dark|neutral|soft-brand|soft-emerald|soft-sky|soft-amber|soft-rose|white
    compact: label disembunyikan di ponsel (ikon saja). href → <a>, selain itu <button>.
--}}
@props(['tone' => 'neutral', 'icon' => null, 'href' => null, 'type' => 'button', 'compact' => false])
@php
    $tones = [
        'primary' => 'bg-brand-700 text-white hover:bg-brand-800 shadow-sm shadow-brand-900/20',
        'dark' => 'bg-zinc-800 text-white hover:bg-zinc-900',
        'neutral' => 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200',
        'white' => 'bg-white text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50',
        'soft-brand' => 'bg-brand-50 text-brand-700 hover:bg-brand-100',
        'soft-emerald' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
        'soft-sky' => 'bg-sky-50 text-sky-700 hover:bg-sky-100',
        'soft-amber' => 'bg-amber-50 text-amber-700 hover:bg-amber-100',
        'soft-rose' => 'bg-rose-50 text-rose-700 hover:bg-rose-100',
        'glass' => 'bg-white/15 text-white ring-1 ring-inset ring-white/30 hover:bg-white/25',
        'amber' => 'bg-amber-300 text-brand-950 hover:bg-amber-200',
    ];
    $classes = 'inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-xl px-3.5 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 '.($tones[$tone] ?? $tones['neutral']);
@endphp
@if ($href)
<a href="{{ $href }}" {{ $attributes->class($classes) }}>
    @if ($icon)<x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4 shrink-0" />@endif
    <span @class(['hidden sm:inline' => $compact])>{{ $slot }}</span>
</a>
@else
<button type="{{ $type }}" {{ $attributes->class($classes) }}>
    @if ($icon)<x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4 shrink-0" />@endif
    <span @class(['hidden sm:inline' => $compact])>{{ $slot }}</span>
</button>
@endif
