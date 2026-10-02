@props(['label'])
<div {{ $attributes }}>
    <dt class="text-xs text-base-content/60">{{ $label }}</dt>
    <dd class="text-sm font-medium break-words">{{ $slot }}</dd>
</div>
