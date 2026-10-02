<x-layouts.app title="Detail Perhitungan MOORA" :subtitle="$periode->nama">
    @php
        $f4 = fn ($v) => number_format((float) $v, 4, ',', '.');
        $ambang = $periode->snapshot_ambang ?? [];
        $tabs = ['matriks' => ['Matriks keputusan', 'table-cells'], 'normalisasi' => ['Normalisasi', 'variable'], 'terbobot' => ['Terbobot', 'scale'], 'hasil' => ['Hasil & peringkat', 'trophy']];
    @endphp

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('peringkat.index', $periode) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Langkah perhitungan MOORA</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $hasil->count() }} alternatif × {{ $kriteria->count() }} kriteria · dihitung {{ $periode->dihitung_pada?->translatedFormat('d M Y H:i') ?? '—' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5">
                @foreach ($kriteria as $k)
                <x-ui.badge :tone="$k['tipe'] === 'cost' ? 'warning' : 'success'"><span class="font-mono">{{ $k['kode'] }}</span> {{ round($k['bobot'] * 100, 2) }}% {{ $k['tipe'] === 'cost' ? 'cost' : 'benefit' }}</x-ui.badge>
                @endforeach
            </div>
        </div>
        <div class="grid grid-cols-1 gap-2 border-t border-zinc-100 bg-zinc-50/60 p-4 text-xs leading-5 text-zinc-600 sm:grid-cols-2 sm:px-5 xl:grid-cols-4">
            <p><strong class="text-zinc-800">1.</strong> Penyebut d<sub>j</sub> = √Σ x<sub>ij</sub>²</p>
            <p><strong class="text-zinc-800">2.</strong> Normalisasi x*<sub>ij</sub> = x<sub>ij</sub> / d<sub>j</sub></p>
            <p><strong class="text-zinc-800">3.</strong> Terbobot v<sub>ij</sub> = w<sub>j</sub> · x*<sub>ij</sub></p>
            <p><strong class="text-zinc-800">4.</strong> Y<sub>i</sub> = Σ v benefit − Σ v cost</p>
        </div>
    </section>

    <section x-data="{ tab: 'matriks' }" class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <div class="grid grid-cols-2 gap-1 border-b border-zinc-200/80 bg-zinc-50 p-2 sm:flex">
            @foreach ($tabs as $key => [$label, $icon])
            <button type="button" @click="tab = '{{ $key }}'" class="flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-xs font-bold transition"
                :class="tab === '{{ $key }}' ? 'bg-white text-brand-700 shadow-sm ring-1 ring-zinc-200' : 'text-zinc-500 hover:text-zinc-800'">
                <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4" /> {{ $label }}
            </button>
            @endforeach
        </div>

        @php
            $th = 'whitespace-nowrap px-3 py-3 text-right font-mono';
            $td = 'whitespace-nowrap px-3 py-2.5 text-right font-mono text-xs tabular-nums';
        @endphp
        <div class="overflow-x-auto">
            {{-- Matriks --}}
            <table x-show="tab === 'matriks'" class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500"><tr><th class="px-3 py-3">Alternatif</th>@foreach ($kriteria as $k)<th class="{{ $th }}">{{ $k['kode'] }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($hasil as $h)
                    <tr class="hover:bg-zinc-50/80"><td class="max-w-[16rem] truncate px-3 py-2.5 font-semibold text-zinc-800" title="{{ $h->aset?->nama_barang }}">{{ $h->aset?->nama_barang }}</td>@foreach ($kriteria as $k)<td class="{{ $td }}">{{ rtrim(rtrim(number_format((float) ($matriks[$h->aset_id][$k['id']] ?? 0), 2, ',', ''), '0'), ',') }}</td>@endforeach</tr>
                    @endforeach
                    <tr class="bg-brand-50/60 font-bold"><td class="px-3 py-2.5 text-xs text-brand-800">Penyebut d<sub>j</sub></td>@foreach ($kriteria as $k)<td class="{{ $td }} text-brand-800">{{ number_format($penyebut[$k['id']], 6, ',', '.') }}</td>@endforeach</tr>
                </tbody>
            </table>
            {{-- Normalisasi --}}
            <table x-show="tab === 'normalisasi'" x-cloak class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500"><tr><th class="px-3 py-3">Alternatif</th>@foreach ($kriteria as $k)<th class="{{ $th }}">x* {{ $k['kode'] }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($hasil as $h)
                    <tr class="hover:bg-zinc-50/80"><td class="max-w-[16rem] truncate px-3 py-2.5 font-semibold text-zinc-800">{{ $h->aset?->nama_barang }}</td>@foreach ($kriteria as $k)<td class="{{ $td }}">{{ $f4($h->detail['normalized'][$k['id']] ?? 0) }}</td>@endforeach</tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Terbobot --}}
            <table x-show="tab === 'terbobot'" x-cloak class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500"><tr><th class="px-3 py-3">Alternatif</th>@foreach ($kriteria as $k)<th class="{{ $th }}">v {{ $k['kode'] }} <span class="normal-case text-zinc-400">(w {{ $f4($k['bobot']) }})</span></th>@endforeach<th class="{{ $th }}">Σ benefit</th><th class="{{ $th }}">Σ cost</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($hasil as $h)
                    <tr class="hover:bg-zinc-50/80"><td class="max-w-[16rem] truncate px-3 py-2.5 font-semibold text-zinc-800">{{ $h->aset?->nama_barang }}</td>@foreach ($kriteria as $k)<td class="{{ $td }}">{{ $f4($h->detail['weighted'][$k['id']] ?? 0) }}</td>@endforeach<td class="{{ $td }} text-emerald-700">{{ $f4($h->detail['benefit_sum'] ?? 0) }}</td><td class="{{ $td }} text-amber-700">{{ $f4($h->detail['cost_sum'] ?? 0) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Hasil --}}
            <table x-show="tab === 'hasil'" x-cloak class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500"><tr><th class="px-3 py-3 text-center">Rank</th><th class="px-3 py-3">Alternatif</th><th class="{{ $th }}">Y<sub>i</sub></th><th class="{{ $th }}">Skor relatif</th><th class="px-3 py-3">Rekomendasi</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($hasil as $h)
                    <tr class="hover:bg-zinc-50/80"><td class="px-3 py-2"><x-ui.rank :ranking="$h->ranking" class="mx-auto" /></td><td class="max-w-[18rem] truncate px-3 py-2.5 font-semibold text-zinc-800">{{ $h->aset?->nama_barang }}</td><td class="{{ $td }} font-bold text-zinc-900">{{ $f4($h->yi) }}</td><td class="{{ $td }}">{{ number_format($h->skor_relatif, 2, ',', '.') }}</td><td class="px-3 py-2.5"><x-ui.badge-tindakan :tindakan="$h->rekomendasi" /></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="border-t border-zinc-100 px-4 py-3 text-xs text-zinc-500 sm:px-5">
            Skor relatif = (Y<sub>i</sub> − Y<sub>min</sub>) / (Y<sub>max</sub> − Y<sub>min</sub>) × 100.
            Ambang saat dihitung: Pertahankan ≥ {{ $ambang['pertahankan'] ?? '—' }}, Hapus &lt; {{ $ambang['perbaiki'] ?? '—' }}, selebihnya Perbaiki.
        </p>
    </section>
</x-layouts.app>
