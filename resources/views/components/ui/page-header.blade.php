@props(['title', 'subtitle' => null])
<div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold text-base-content">{{ $title }}</h1>
        @if ($subtitle)
        <p class="text-sm text-base-content/60 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
    <div class="flex flex-wrap gap-2 mt-2 md:mt-0">
        {{ $actions }}
    </div>
    @endisset
</div>
