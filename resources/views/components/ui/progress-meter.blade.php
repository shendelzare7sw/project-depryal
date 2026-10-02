{{-- strict: hijau hanya jika tepat = max, selain itu merah (mis. total bobot kriteria harus 100%) --}}
@props(['value' => 0, 'max' => 100, 'label' => '', 'strict' => false])
@php
    $pct = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;
    $tepat = abs($value - $max) < 0.0001;
    $tone = $strict ? ($tepat ? 'success' : 'error') : ($pct >= 100 ? 'success' : 'primary');
    $text = ['success' => 'text-emerald-700', 'error' => 'text-rose-600', 'primary' => 'text-zinc-500'][$tone];
@endphp
<div class="w-full">
    <div class="mb-2 flex items-baseline justify-between gap-3 text-xs">
        <span class="font-semibold text-zinc-700">{{ $label }}</span>
        <span class="font-bold tabular-nums {{ $text }}">{{ $value }} / {{ $max }} <span class="font-medium opacity-70">({{ $pct }}%)</span></span>
    </div>
    <progress class="progress progress-{{ $tone }} h-2 w-full bg-zinc-100" value="{{ $pct }}" max="100"></progress>
</div>
