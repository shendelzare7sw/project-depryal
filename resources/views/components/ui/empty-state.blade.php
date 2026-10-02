@props(['icon' => 'inbox', 'title', 'text' => ''])
<div class="flex flex-col items-center justify-center gap-2 px-4 py-12 text-center">
    <span class="mb-2 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 ring-8 ring-brand-50/50">
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="h-7 w-7" />
    </span>
    <p class="text-base font-bold text-zinc-900">{{ $title }}</p>
    @if ($text)
    <p class="max-w-sm text-sm text-zinc-500">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
    <div class="mt-3 flex flex-wrap justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
