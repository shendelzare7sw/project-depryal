{{-- strict: hijau hanya jika tepat = max, selain itu merah (mis. total bobot kriteria harus 100%) --}}
@props(['value' => 0, 'max' => 100, 'label' => '', 'strict' => false])
@php
    $pct = $max > 0 ? min(100, round(($value / $max) * 100)) : 0;
    $tepat = abs($value - $max) < 0.0001;
    $tone = $strict ? ($tepat ? 'success' : 'error') : ($pct >= 100 ? 'success' : 'primary');
@endphp
<div class="w-full">
    <div class="flex justify-between text-xs mb-1">
        <span class="font-medium">{{ $label }}</span>
        <span class="{{ $tone === 'primary' ? 'text-base-content/60' : 'text-' . $tone . ' font-bold' }}">
            {{ $value }} / {{ $max }} ({{ $pct }}%)
        </span>
    </div>
    <progress class="progress progress-{{ $tone }} w-full" value="{{ $pct }}" max="100"></progress>
</div>
