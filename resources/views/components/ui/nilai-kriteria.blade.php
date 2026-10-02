{{-- Nilai tiap kriteria (snapshot saat dihitung) + label rubrik, nilai ternormalisasi & terbobot. --}}
@props(['kriteria'])
<ul {{ $attributes->class(['divide-y divide-zinc-100']) }}>
    @foreach ($kriteria as $k)
    <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
        <span class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-xl {{ $k['tipe'] === 'cost' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
            <span class="text-lg font-extrabold leading-none">{{ $k['nilai'] !== null ? rtrim(rtrim(number_format($k['nilai'], 2, ',', ''), '0'), ',') : '–' }}</span>
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-zinc-900"><span class="font-mono text-xs text-zinc-400">{{ $k['kode'] }}</span> {{ $k['nama'] }}</p>
            <p class="truncate text-xs text-zinc-500">{{ $k['label'] ?? 'Tanpa label rubrik' }} · {{ $k['tipe'] === 'cost' ? 'Cost' : 'Benefit' }} · bobot {{ round($k['bobot'] * 100, 2) }}%</p>
        </div>
        <div class="hidden shrink-0 text-right sm:block">
            <p class="font-mono text-xs font-bold tabular-nums text-zinc-800">{{ number_format($k['weighted'], 4, ',', '.') }}</p>
            <p class="text-[10px] text-zinc-400">x* {{ number_format($k['normalized'], 4, ',', '.') }}</p>
        </div>
    </li>
    @endforeach
</ul>
