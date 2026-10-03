<x-layouts.app title="Beranda" subtitle="Ringkasan keputusan kelayakan aset BMD">
    @php
        $user = auth()->user();
        $bar = ['pertahankan' => 'bg-emerald-500', 'perbaiki' => 'bg-amber-400', 'hapus' => 'bg-rose-500'];
        $dihitung = $periode?->status->value === 'dihitung';
        $subjudul = ! $periode
            ? 'Belum ada periode yang dihitung. Hasil MOORA akan muncul setelah operator menyelesaikan penilaian.'
            : ($dihitung
                ? ($menunggu > 0 ? "{$menunggu} aset menunggu keputusan Anda pada periode \"{$periode->nama}\"." : 'Semua aset sudah diputuskan — periode siap difinalisasi.')
                : "Periode terakhir \"{$periode->nama}\" sudah final.");
    @endphp

    <x-ui.hero :eyebrow="'SIKASET · '.now()->translatedFormat('l, d F Y')" :title="'Halo, '.$user->name.'!'" :subtitle="$subjudul">
        <x-slot:actions>
            @if ($dihitung && $belumPertama)
            <x-ui.btn tone="amber" icon="check-badge" :href="route('keputusan.edit', [$periode, $belumPertama])">Mulai Putuskan</x-ui.btn>
            @elseif ($dihitung && $bisaFinalisasi)
            <x-ui.btn tone="amber" icon="lock-closed" :href="route('peringkat.index', $periode)">Tinjau & Finalisasi</x-ui.btn>
            @endif
            @if ($periode)
            <x-ui.btn tone="glass" icon="chart-bar" :href="route('peringkat.index', $periode)">Lihat Peringkat</x-ui.btn>
            @endif
            <x-ui.btn tone="glass" icon="document-chart-bar" :href="route('laporan.index')">Laporan</x-ui.btn>
        </x-slot:actions>
        @if ($periode)
        <x-slot:aside>
            <div class="w-full rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/20 lg:w-64">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-100">Menunggu keputusan</p>
                <p class="mt-1 text-4xl font-extrabold !text-white">{{ $menunggu }}<span class="text-base font-bold text-brand-100"> / {{ $total }} aset</span></p>
                <progress class="progress mt-3 h-2 w-full bg-white/20 [&::-webkit-progress-value]:bg-amber-300 [&::-moz-progress-bar]:bg-amber-300" value="{{ $diputuskan }}" max="{{ max(1, $total) }}"></progress>
                <p class="mt-2 text-xs text-brand-50/90">{{ $diputuskan }} sudah diputuskan</p>
            </div>
        </x-slot:aside>
        @endif
    </x-ui.hero>

    @if (! $periode)
    <x-ui.card>
        <x-ui.empty-state icon="chart-bar" title="Belum ada hasil penilaian" text="Anda akan mendapat notifikasi saat peringkat MOORA siap ditinjau." />
    </x-ui.card>
    @else
    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-5">
            {{-- Distribusi rekomendasi --}}
            <x-ui.card title="Distribusi rekomendasi sistem" icon="chart-pie" :subtitle="$periode->nama.' · '.$total.' aset'">
                <div class="flex h-4 w-full overflow-hidden rounded-full bg-zinc-100" role="img" aria-label="Distribusi rekomendasi">
                    @foreach ($distribusi as $d)
                    @if ($d['jumlah'] > 0)<span class="{{ $bar[$d['tindakan']->value] }} h-full" style="width: {{ $d['persen'] }}%" title="{{ $d['tindakan']->label() }}: {{ $d['jumlah'] }}"></span>@endif
                    @endforeach
                </div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($distribusi as $d)
                    <a href="{{ route('peringkat.index', [$periode, 'rekomendasi' => $d['tindakan']->value]) }}" class="rounded-xl border border-zinc-200/80 p-3 transition hover:border-zinc-300 hover:bg-zinc-50">
                        <p class="flex items-center gap-1.5 text-[11px] font-bold text-zinc-500"><span class="h-2 w-2 rounded-full {{ $bar[$d['tindakan']->value] }}"></span>{{ $d['tindakan']->label() }}</p>
                        <p class="mt-1 text-xl font-extrabold tabular-nums text-zinc-900">{{ $d['jumlah'] }}</p>
                        <p class="text-[11px] text-zinc-400">{{ $d['persen'] }}%</p>
                    </a>
                    @endforeach
                </div>
            </x-ui.card>

            {{-- 5 aset prioritas --}}
            <x-ui.card :title="$menunggu > 0 ? 'Prioritas untuk diputuskan' : 'Skor terendah'" icon="flag" icon-tone="text-rose-500" :link="route('peringkat.index', $periode)" flush>
                <x-slot:chip><x-ui.badge tone="error">5 terbawah</x-ui.badge></x-slot:chip>
                <div class="divide-y divide-zinc-100">
                    @foreach ($prioritas as $b)
                    <a href="{{ $dihitung ? route('keputusan.edit', [$periode, $b['aset']]) : route('peringkat.show', [$periode, $b['aset']]) }}" class="group flex items-center gap-3 px-4 py-3 transition hover:bg-zinc-50 sm:px-5">
                        <x-ui.rank :ranking="$b['hasil']->ranking" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-zinc-900 group-hover:text-brand-700">{{ $b['aset']->nama_barang }}</span>
                            <x-ui.skor :value="$b['hasil']->skor_relatif" :tindakan="$b['hasil']->rekomendasi" class="mt-1" />
                        </span>
                        <span class="hidden shrink-0 sm:block"><x-ui.badge-tindakan :tindakan="$b['hasil']->rekomendasi" /></span>
                        <x-heroicon-m-chevron-right class="h-4 w-4 shrink-0 text-zinc-300 group-hover:text-brand-600" />
                    </a>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <aside class="min-w-0 space-y-5">
            <x-ui.card title="Periode final" icon="lock-closed" icon-tone="text-emerald-600">
                <ul class="space-y-2.5">
                    @forelse ($riwayat as $p)
                    <li><a href="{{ route('peringkat.index', $p) }}" class="flex items-center gap-3 rounded-xl p-2 hover:bg-zinc-50">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-heroicon-o-check-circle class="h-5 w-5" /></span>
                        <span class="min-w-0"><span class="block truncate text-sm font-semibold text-zinc-800">{{ $p->nama }}</span><span class="block text-[11px] text-zinc-500">{{ $p->aset_count }} aset · {{ $p->difinalisasi_pada?->translatedFormat('d M Y') }}</span></span>
                    </a></li>
                    @empty
                    <li class="text-xs text-zinc-500">Belum ada periode final.</li>
                    @endforelse
                </ul>
            </x-ui.card>
            <x-ui.card title="Laporan terbaru" icon="document-chart-bar" icon-tone="text-sky-600" :link="route('laporan.index')">
                <ul class="space-y-2.5">
                    @forelse ($laporan as $l)
                    <li class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $l->format === \App\Enums\FormatLaporan::Pdf ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} text-[10px] font-extrabold uppercase">{{ $l->format->value }}</span>
                        <span class="min-w-0"><span class="block truncate text-xs font-semibold text-zinc-800">{{ $l->jenis->label() }}</span><span class="block truncate text-[11px] text-zinc-500">{{ $l->periode?->nama }} · {{ $l->created_at?->diffForHumans() }}</span></span>
                    </li>
                    @empty
                    <li class="text-xs text-zinc-500">Belum ada laporan dicetak.</li>
                    @endforelse
                </ul>
            </x-ui.card>
        </aside>
    </div>
    @endif
</x-layouts.app>
