@props(['label'])
<div {{ $attributes }}>
    <dt class="text-xs font-medium text-zinc-500">{{ $label }}</dt>
    <dd class="mt-0.5 break-words text-sm font-semibold text-zinc-900">{{ $slot }}</dd>
</div>
