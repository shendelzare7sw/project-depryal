{{-- Badge lembut. tone: success|warning|error|info|primary|secondary|accent|neutral|ghost --}}
@props(['tone' => 'neutral', 'dot' => false])
@php
    $tones = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/25',
        'error' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'primary' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
        'secondary' => 'bg-amber-50 text-amber-800 ring-amber-600/25',
        'accent' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-500/15',
        'ghost' => 'bg-zinc-50 text-zinc-500 ring-zinc-400/20',
    ];
    $dots = [
        'success' => 'bg-emerald-500', 'warning' => 'bg-amber-500', 'error' => 'bg-rose-500', 'info' => 'bg-sky-500',
        'primary' => 'bg-brand-500', 'secondary' => 'bg-amber-500', 'accent' => 'bg-violet-500', 'neutral' => 'bg-zinc-400', 'ghost' => 'bg-zinc-300',
    ];
@endphp
<span {{ $attributes->class(['inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset', $tones[$tone] ?? $tones['neutral']]) }}>
    @if ($dot)<span class="h-1.5 w-1.5 rounded-full {{ $dots[$tone] ?? $dots['neutral'] }}"></span>@endif
    {{ $slot }}
</span>
