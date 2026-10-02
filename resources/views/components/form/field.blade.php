@props(['name', 'label', 'hint' => null, 'required' => false])
@php($errorKey = str_replace(['[', ']'], ['.', ''], $name))
<div {{ $attributes->class(['w-full']) }}>
    <label class="mb-1.5 flex items-baseline justify-between gap-2" for="{{ $name }}">
        <span class="text-sm font-semibold text-zinc-700">
            {{ $label }}@if ($required)<span class="ml-0.5 text-rose-500">*</span>@endif
        </span>
        @if ($hint)
        <span class="truncate text-xs text-zinc-400">{{ $hint }}</span>
        @endif
    </label>
    {{ $slot }}
    @error($errorKey)
    <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600">
        <x-heroicon-m-exclamation-circle class="h-4 w-4 shrink-0" /> {{ $message }}
    </p>
    @enderror
</div>
