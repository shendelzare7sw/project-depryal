@props(['label', 'value', 'icon' => null, 'tone' => 'primary'])
<div class="stat bg-base-100 rounded-box shadow-sm border border-base-200">
    @if ($icon)
    <div class="stat-figure text-{{ $tone }}">
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-8 h-8" />
    </div>
    @endif
    <div class="stat-title text-xs">{{ $label }}</div>
    <div class="stat-value text-2xl text-{{ $tone }}">{{ $value }}</div>
</div>
