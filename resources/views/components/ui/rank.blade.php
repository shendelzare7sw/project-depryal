{{-- Lencana peringkat: 1–3 diberi warna medali. --}}
@props(['ranking', 'size' => 'md'])
@php
    $tone = [1 => 'bg-amber-400 text-amber-950', 2 => 'bg-zinc-300 text-zinc-800', 3 => 'bg-orange-300 text-orange-950'][$ranking] ?? 'bg-zinc-100 text-zinc-600';
    $dim = $size === 'lg' ? 'h-14 w-14 text-xl rounded-2xl' : 'h-10 w-10 text-sm rounded-xl';
@endphp
<span {{ $attributes->class(['flex shrink-0 flex-col items-center justify-center font-extrabold leading-none tabular-nums', $dim, $tone]) }} title="Peringkat {{ $ranking }}">
    {{ $ranking }}
</span>
