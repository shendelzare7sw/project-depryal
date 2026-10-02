<x-layouts.app title="Beranda" subtitle="Ringkasan kelayakan aset BMD Kecamatan Batuceper">
    @php
        $user = auth()->user();
        $selesai = collect($langkah)->where('done', true)->count();
        $pesanRole = [
            'operator' => 'Kelola data aset, kriteria, dan penilaian hingga peringkat MOORA siap diputuskan.',
            'pimpinan' => 'Tinjau peringkat dan rekomendasi sistem, lalu tetapkan tindakan untuk setiap aset.',
            'admin' => 'Pantau pengguna, pengaturan ambang rekomendasi, dan jejak aktivitas sistem.',
        ][$user->role->value];
    @endphp

    <x-ui.hero :eyebrow="'SIKASET · '.now()->translatedFormat('l, d F Y')" :title="'Halo, '.$user->name.'!'" :subtitle="$pesanRole">
        <x-slot:actions>
            @if ($user->isOperator())
            <x-ui.btn tone="amber" icon="plus" :href="route('aset.create')">Tambah Aset</x-ui.btn>
            <x-ui.btn tone="glass" icon="arrow-up-tray" :href="route('aset.import')">Import BMD</x-ui.btn>
            @else
            <x-ui.btn tone="amber" icon="building-office-2" :href="route('aset.index')">Lihat Data Aset</x-ui.btn>
            @endif
            <x-ui.btn tone="glass" icon="scale" :href="route('kriteria.index')">Kriteria</x-ui.btn>
        </x-slot:actions>
        <x-slot:aside>
            <div class="hidden w-56 rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/20 lg:block">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-100">Progres alur SPK</p>
                <p class="mt-1 text-3xl font-extrabold !text-white">{{ $selesai }}<span class="text-base font-bold text-brand-100">/{{ count($langkah) }}</span></p>
                <progress class="progress mt-3 h-2 w-full bg-white/20 [&::-webkit-progress-value]:bg-amber-300 [&::-moz-progress-bar]:bg-amber-300" value="{{ $selesai }}" max="{{ count($langkah) }}"></progress>
                <p class="mt-2 text-xs text-brand-50/90">langkah selesai</p>
            </div>
        </x-slot:aside>
    </x-ui.hero>

    <x-ui.stat-grid :items="$stats" />

    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-5">
            {{-- Langkah kerja --}}
            <x-ui.card title="Alur kerja penilaian" icon="map" subtitle="Ikuti urutan ini dari data hingga keputusan">
                <x-slot:chip><x-ui.badge tone="primary">{{ $selesai }}/{{ count($langkah) }}</x-ui.badge></x-slot:chip>
                <ol class="space-y-1">
                    @foreach ($langkah as $i => $step)
                    @php($tag = $step['href'] ? 'a' : 'div')
                    <li>
                        <{{ $tag }} @if ($step['href']) href="{{ $step['href'] }}" @endif class="group flex items-center gap-3 rounded-xl p-2 transition hover:bg-brand-50/60 sm:p-3">
                            @if ($step['done'])
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><x-heroicon-s-check class="h-5 w-5" /></span>
                            @else
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-sm font-extrabold text-zinc-500">{{ $i + 1 }}</span>
                            @endif
                            <span class="h-9 w-1 shrink-0 rounded-full {{ $step['done'] ? 'bg-gradient-to-b from-emerald-400 to-emerald-600' : 'bg-zinc-200' }}" aria-hidden="true"></span>
                            <span class="min-w-0 flex-1">
                                <span @class(['block truncate text-sm font-bold', 'text-zinc-900 group-hover:text-brand-700' => ! $step['done'], 'text-zinc-500 line-through decoration-zinc-300' => $step['done']])>{{ $step['label'] }}</span>
                                <span class="block truncate text-xs text-zinc-500">{{ $step['hint'] }}</span>
                            </span>
                            @if ($step['href'])<x-heroicon-m-chevron-right class="h-4 w-4 shrink-0 text-zinc-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600" />@endif
                        </{{ $tag }}>
                    </li>
                    @endforeach
                </ol>
            </x-ui.card>

            {{-- Aset perlu perhatian --}}
            <x-ui.card title="Aset perlu perhatian" icon="exclamation-triangle" icon-tone="text-amber-500" :link="route('aset.index')" flush>
                <x-slot:chip><x-ui.badge tone="warning">Sisa UEB ≤ 3</x-ui.badge></x-slot:chip>
                <div class="divide-y divide-zinc-100">
                    @forelse ($asetPerhatian as $aset)
                    <a href="{{ route('aset.show', $aset) }}" class="group flex items-center gap-3 px-4 py-3 transition hover:bg-zinc-50 sm:px-5">
                        <span class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <span class="text-sm font-extrabold leading-none">{{ $aset->sisa_ueb }}</span>
                            <span class="text-[9px] font-bold uppercase">thn</span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-zinc-900 group-hover:text-brand-700">{{ $aset->nama_barang }}</span>
                            <span class="block truncate text-xs text-zinc-500">{{ $aset->kategori?->nama }} · {{ $aset->kode_barang }}/{{ $aset->nup }}</span>
                        </span>
                        <x-ui.badge-status :status="$aset->status" />
                    </a>
                    @empty
                    <div class="flex items-center gap-3 px-5 py-6 text-sm text-zinc-500">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><x-heroicon-o-shield-check class="h-5 w-5" /></span>
                        Tidak ada aset dengan sisa umur ekonomis ≤ 3 tahun.
                    </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="min-w-0 space-y-5">
            {{-- Bobot kriteria --}}
            <x-ui.card title="Bobot kriteria" icon="scale" :link="route('kriteria.index')" linkLabel="Kelola">
                <x-ui.progress-meter :value="$totalBobot" :max="100" label="Total bobot aktif (%)" strict />
                <ul class="space-y-3 pt-1">
                    @forelse ($kriteria as $k)
                    <li>
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="rounded-md bg-zinc-100 px-1.5 py-0.5 font-mono text-[10px] font-bold text-zinc-600">{{ $k->kode }}</span>
                                <span class="truncate font-semibold text-zinc-700">{{ $k->nama }}</span>
                            </span>
                            <span class="shrink-0 font-bold tabular-nums text-zinc-900">{{ $k->bobotPersen() }}%</span>
                        </div>
                        <progress @class(['progress mt-1.5 h-1.5 w-full bg-zinc-100', 'progress-success' => $k->tipe->value === 'benefit', 'progress-warning' => $k->tipe->value === 'cost']) value="{{ $k->bobotPersen() }}" max="100"></progress>
                    </li>
                    @empty
                    <li class="text-xs text-zinc-500">Belum ada kriteria aktif.</li>
                    @endforelse
                </ul>
                <p class="flex items-center gap-3 border-t border-zinc-100 pt-3 text-[11px] text-zinc-500">
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Benefit</span>
                    <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Cost</span>
                </p>
            </x-ui.card>

            {{-- Komposisi kategori --}}
            <x-ui.card title="Aset per kategori" icon="tag" icon-tone="text-violet-500">
                <ul class="space-y-2.5">
                    @forelse ($kategori as $kat)
                    <li class="flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-xs font-extrabold text-violet-700">{{ $kat->aset_count }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-semibold text-zinc-700">{{ $kat->nama }}</span>
                            <progress class="progress mt-1 h-1 w-full bg-zinc-100 [&::-webkit-progress-value]:bg-violet-400 [&::-moz-progress-bar]:bg-violet-400" value="{{ $kat->aset_count }}" max="{{ max(1, $totalAset) }}"></progress>
                        </span>
                    </li>
                    @empty
                    <li class="text-xs text-zinc-500">Belum ada kategori.</li>
                    @endforelse
                </ul>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
