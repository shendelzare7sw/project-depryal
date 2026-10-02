{{-- Hero gradien untuk dashboard/halaman kunci. Slot opsional: actions, aside (kanan, desktop). --}}
@props(['title', 'eyebrow' => null, 'subtitle' => null])
<section {{ $attributes->class(['relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-800 via-brand-700 to-brand-600 p-5 text-white shadow-lg shadow-brand-900/15 sm:p-6 lg:p-7']) }}>
    <span class="pointer-events-none absolute -right-12 -top-16 h-52 w-52 rounded-full bg-white/10" aria-hidden="true"></span>
    <span class="pointer-events-none absolute -bottom-20 right-24 h-40 w-40 rounded-full bg-amber-300/20" aria-hidden="true"></span>
    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            @if ($eyebrow)<p class="text-xs font-bold text-brand-100">{{ $eyebrow }}</p>@endif
            <h2 class="mt-1 text-xl font-extrabold !text-white sm:text-2xl">{{ $title }}</h2>
            @if ($subtitle)<p class="mt-2 max-w-2xl text-xs leading-5 text-brand-50/90 sm:text-sm">{{ $subtitle }}</p>@endif
            @isset($actions)<div class="mt-4 flex flex-wrap gap-2">{{ $actions }}</div>@endisset
        </div>
        @isset($aside)<div class="relative shrink-0">{{ $aside }}</div>@endisset
    </div>
</section>
