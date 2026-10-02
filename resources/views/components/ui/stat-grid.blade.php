{{--
    Grid statistik: 2 kolom di ponsel, $columns kolom di desktop (3/4/6). Ikon selalu tampil.
    items: list<array{label, value, icon, tone, meta?, href?}>  tone: brand|amber|emerald|rose|sky|violet|zinc
--}}
@props(['items', 'columns' => 4])
@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700', 'amber' => 'bg-amber-50 text-amber-600', 'emerald' => 'bg-emerald-50 text-emerald-600',
        'rose' => 'bg-rose-50 text-rose-600', 'sky' => 'bg-sky-50 text-sky-600', 'violet' => 'bg-violet-50 text-violet-600', 'zinc' => 'bg-zinc-100 text-zinc-600',
    ];
    $cols = [3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 6 => 'lg:grid-cols-6'][(int) $columns] ?? 'lg:grid-cols-4';
@endphp
<section {{ $attributes->class(['grid min-w-0 grid-cols-2 gap-2 sm:gap-3', $cols]) }}>
    @foreach ($items as $item)
    <article @class(['min-w-0 rounded-2xl border border-zinc-200/80 bg-white p-3 shadow-sm shadow-zinc-900/[0.03] sm:p-4', 'col-span-2 lg:col-span-1' => $loop->last && count($items) % 2 === 1])>
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="truncate text-xl font-extrabold leading-none tabular-nums text-zinc-900 sm:text-2xl" title="{{ $item['value'] }}">{{ $item['value'] }}</p>
                <p class="mt-2 text-[10px] font-bold uppercase leading-4 tracking-wide text-zinc-500 sm:truncate sm:text-[11px]" title="{{ $item['label'] }}">{{ $item['label'] }}</p>
            </div>
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl sm:h-10 sm:w-10 {{ $tones[$item['tone'] ?? 'brand'] ?? $tones['brand'] }}">
                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-5 w-5" />
            </span>
        </div>
        @if (! empty($item['meta']))
        <p class="mt-3 truncate border-t border-zinc-100 pt-2.5 text-[11px] text-zinc-500 sm:text-xs" title="{{ $item['meta'] }}">
            @if (! empty($item['href']))<a href="{{ $item['href'] }}" class="font-semibold text-brand-700 hover:text-brand-900">{{ $item['meta'] }}</a>@else{{ $item['meta'] }}@endif
        </p>
        @endif
    </article>
    @endforeach
</section>
