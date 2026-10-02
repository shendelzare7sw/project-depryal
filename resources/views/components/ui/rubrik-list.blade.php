{{-- Rubrik skala kriteria (lipat, tanpa JS) --}}
@props(['kriteria'])
<details {{ $attributes->class(['group text-xs']) }}>
    <summary class="inline-flex cursor-pointer select-none list-none items-center gap-1 font-bold text-brand-700 hover:text-brand-900 [&::-webkit-details-marker]:hidden">
        <x-heroicon-m-chevron-right class="h-3.5 w-3.5 transition group-open:rotate-90" />
        Rubrik skala {{ $kriteria->skala_min }}–{{ $kriteria->skala_maks }}
    </summary>
    <ul class="mt-2 space-y-1.5 rounded-xl bg-zinc-50 p-3">
        @foreach ($kriteria->skala as $skala)
        <li class="flex gap-2">
            <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-white text-[10px] font-extrabold text-brand-700 ring-1 ring-zinc-200">{{ $skala->nilai }}</span>
            <span class="leading-5 text-zinc-600"><span class="font-bold text-zinc-800">{{ $skala->label }}</span>@if ($skala->deskripsi) — {{ $skala->deskripsi }}@endif</span>
        </li>
        @endforeach
    </ul>
</details>
