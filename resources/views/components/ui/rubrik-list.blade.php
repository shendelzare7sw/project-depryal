{{-- Rubrik skala kriteria (lipat, tanpa JS) --}}
@props(['kriteria'])
<details class="mt-1 text-xs">
    <summary class="cursor-pointer text-primary select-none">Rubrik skala {{ $kriteria->skala_min }}–{{ $kriteria->skala_maks }}</summary>
    <ul class="mt-2 space-y-1">
        @foreach ($kriteria->skala as $skala)
        <li class="flex gap-2">
            <span class="badge badge-outline badge-sm shrink-0">{{ $skala->nilai }}</span>
            <span><span class="font-semibold">{{ $skala->label }}</span>@if ($skala->deskripsi) — {{ $skala->deskripsi }}@endif</span>
        </li>
        @endforeach
    </ul>
</details>
