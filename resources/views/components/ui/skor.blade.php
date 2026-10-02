{{-- Skor relatif 0–100 sebagai bar berwarna sesuai rekomendasi. --}}
@props(['value', 'tindakan' => null])
@php($tone = $tindakan?->color() ?? 'primary')
<div {{ $attributes->class(['flex min-w-0 items-center gap-2']) }}>
    <progress class="progress progress-{{ $tone }} h-2 flex-1 bg-zinc-100" value="{{ $value }}" max="100"></progress>
    <span class="w-12 shrink-0 text-right text-xs font-extrabold tabular-nums text-zinc-800">{{ number_format((float) $value, 2, ',', '.') }}</span>
</div>
