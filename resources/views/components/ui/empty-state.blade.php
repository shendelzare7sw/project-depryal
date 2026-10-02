@props(['icon' => 'inbox', 'title', 'text' => ''])
<div class="flex flex-col items-center justify-center py-16 gap-3 text-center text-base-content/50">
    <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-14 h-14 opacity-40" />
    <p class="text-base font-semibold text-base-content/70">{{ $title }}</p>
    @if ($text)
    <p class="text-sm max-w-xs">{{ $text }}</p>
    @endif
    @isset($slot)
    <div class="mt-2">{{ $slot }}</div>
    @endisset
</div>
