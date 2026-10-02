{{-- Tombol aksi ikon 36 px untuk tabel desktop. tone: view|edit|delete|success|neutral --}}
@props(['href' => null, 'type' => 'button', 'tone' => 'neutral', 'icon', 'label'])
@php
    $tones = [
        'view' => 'bg-sky-50 text-sky-700 ring-sky-100 hover:bg-sky-100',
        'edit' => 'bg-amber-50 text-amber-700 ring-amber-100 hover:bg-amber-100',
        'delete' => 'bg-rose-50 text-rose-700 ring-rose-100 hover:bg-rose-100',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 hover:bg-emerald-100',
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-200 hover:bg-zinc-200',
    ];
    $classes = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ring-1 ring-inset transition hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 '.($tones[$tone] ?? $tones['neutral']);
@endphp
@if ($href)
<a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
    <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4" />
</a>
@else
<button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class($classes) }}>
    <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4" />
</button>
@endif
