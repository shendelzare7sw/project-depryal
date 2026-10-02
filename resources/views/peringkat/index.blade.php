<x-layouts.app title="Peringkat Aset" :subtitle="$periode->nama">
    @php
        $pimpinan = auth()->user()->isPimpinan();
        $status = $periode->status->value;
        $bisaPutus = $pimpinan && $status === 'dihitung';
        $adaHasil = $total > 0;
        $filter = request()->only(['q', 'rekomendasi', 'keputusan']);
        $tone = ['pertahankan' => 'emerald', 'perbaiki' => 'amber', 'hapus' => 'rose'];
        $stats = [['label' => 'Aset dinilai', 'value' => $total, 'icon' => 'building-office-2', 'tone' => 'brand', 'meta' => $diputuskan.' sudah diputuskan']];
        foreach (\App\Enums\TindakanAset::cases() as $t) {
            $stats[] = ['label' => 'Rekomendasi '.$t->label(), 'value' => $ringkasan[$t->value], 'icon' => $t->icon(), 'tone' => $tone[$t->value],
                'meta' => $total ? round($ringkasan[$t->value] / $total * 100).'% dari aset' : null];
        }
        $subjudul = $periode->isFinal() ? 'Periode final — keputusan terkunci dan status aset sudah diperbarui.' : 'Peringkat dari skor relatif MOORA (0–100). Rekomendasi sistem hanya alat bantu; keputusan akhir di tangan pimpinan.';
        $eyebrow = 'Hasil MOORA · '.$periode->status->label().($periode->dihitung_pada ? ' · dihitung '.$periode->dihitung_pada->translatedFormat('d M Y H:i') : '');
    @endphp

    <x-ui.hero :eyebrow="$eyebrow" :title="$periode->nama" :subtitle="$subjudul">
        <x-slot:actions>
            @if ($adaHasil)
            <x-ui.btn tone="glass" icon="calculator" :href="route('peringkat.detail', $periode)">Detail Perhitungan</x-ui.btn>
            @endif
            @if ($bisaPutus && $belumPertama)
            <x-ui.btn tone="amber" icon="check-badge" :href="route('keputusan.edit', [$periode, $belumPertama])">Mulai Putuskan</x-ui.btn>
            @endif
            @if ($pimpinan && $bisaFinalisasi)
            <x-confirm-form :action="route('periode.finalisasi', $periode)" title="Finalisasi periode?"
                text="Semua nilai, hasil, dan keputusan akan dikunci. Status aset diperbarui sesuai keputusan." confirm="Ya, finalisasi" icon="warning">
                <x-ui.btn type="submit" tone="amber" icon="lock-closed">Finalisasi Periode</x-ui.btn>
            </x-confirm-form>
            @endif
            @if (auth()->user()->isOperator())
            <x-ui.btn tone="glass" icon="calendar-days" :href="route('periode.show', $periode)">Halaman Periode</x-ui.btn>
            @endif
        </x-slot:actions>
        <x-slot:aside>
            <div class="w-full rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/20 lg:w-64">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-100">Keputusan pimpinan</p>
                <p class="mt-1 text-3xl font-extrabold !text-white">{{ $diputuskan }}<span class="text-base font-bold text-brand-100">/{{ $total }}</span></p>
                <progress class="progress mt-3 h-2 w-full bg-white/20 [&::-webkit-progress-value]:bg-amber-300 [&::-moz-progress-bar]:bg-amber-300" value="{{ $diputuskan }}" max="{{ max(1, $total) }}"></progress>
                <p class="mt-2 text-xs text-brand-50/90">{{ $total - $diputuskan > 0 ? ($total - $diputuskan).' aset menunggu keputusan' : 'Semua aset sudah diputuskan' }}</p>
            </div>
        </x-slot:aside>
    </x-ui.hero>

    @if (! $adaHasil)
    <x-ui.card>
        <x-ui.empty-state icon="calculator" title="Periode belum dihitung" text="Peringkat tersedia setelah semua nilai lengkap dan Operator menjalankan Hitung MOORA.">
            <x-ui.btn tone="primary" icon="calendar-days" :href="route('periode.show', $periode)">Lihat Periode</x-ui.btn>
        </x-ui.empty-state>
    </x-ui.card>
    @else
    <x-ui.stat-grid :items="$stats" />

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="border-b border-zinc-200/80 p-4 sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><x-heroicon-o-trophy class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Daftar peringkat</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $baris->count() }} dari {{ $total }} aset · urut skor relatif tertinggi</p>
                </div>
            </div>
            <form method="GET" class="mt-4 grid grid-cols-2 gap-2 xl:grid-cols-[minmax(220px,1fr)_minmax(170px,0.35fr)_minmax(170px,0.35fr)_auto]">
                <label class="relative col-span-2 xl:col-span-1">
                    <span class="sr-only">Cari aset</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode barang…" class="h-10 w-full rounded-xl border border-zinc-200 pl-10 pr-3 text-xs outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </label>
                <select name="rekomendasi" aria-label="Filter rekomendasi" class="h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500">
                    <option value="">Semua rekomendasi</option>
                    @foreach (\App\Enums\TindakanAset::cases() as $t)<option value="{{ $t->value }}" @selected(request('rekomendasi') === $t->value)>{{ $t->label() }}</option>@endforeach
                </select>
                <select name="keputusan" aria-label="Filter keputusan" class="h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500">
                    <option value="">Semua keputusan</option>
                    <option value="belum" @selected(request('keputusan') === 'belum')>Belum diputuskan</option>
                    <option value="sudah" @selected(request('keputusan') === 'sudah')>Sudah diputuskan</option>
                </select>
                <div class="col-span-2 flex gap-2 xl:col-span-1">
                    <x-ui.btn type="submit" tone="dark" icon="funnel" class="flex-1">Filter</x-ui.btn>
                    @if (array_filter($filter))<x-ui.btn tone="soft-rose" icon="x-mark" :href="route('peringkat.index', $periode)" aria-label="Hapus filter"></x-ui.btn>@endif
                </div>
            </form>
        </header>

        <div class="divide-y divide-zinc-100 lg:hidden">
            @forelse ($baris as $b)
            <article class="p-4">
                <div class="flex items-start gap-3">
                    <x-ui.rank :ranking="$b['hasil']->ranking" />
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $b['aset']->nama_barang }}</h3>
                        <p class="mt-0.5 truncate font-mono text-[11px] text-zinc-500">{{ $b['aset']->kode_barang }} · NUP {{ $b['aset']->nup }}</p>
                        <x-ui.skor :value="$b['hasil']->skor_relatif" :tindakan="$b['hasil']->rekomendasi" class="mt-2" />
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-zinc-50 p-3 text-[11px]">
                    <div class="min-w-0"><p class="text-zinc-400">Rekomendasi sistem</p><x-ui.badge-tindakan :tindakan="$b['hasil']->rekomendasi" class="mt-1" /></div>
                    <div class="min-w-0"><p class="text-zinc-400">Keputusan pimpinan</p>
                        @if ($b['keputusan'])<x-ui.badge-tindakan :tindakan="$b['keputusan']->tindakan" class="mt-1" />@else<x-ui.badge tone="ghost" class="mt-1">Belum</x-ui.badge>@endif
                    </div>
                    @if ($b['peringatan'])
                    <p class="col-span-2 flex items-center gap-1.5 font-semibold text-amber-700"><x-heroicon-s-exclamation-triangle class="h-4 w-4 shrink-0" /> {{ \App\Services\Peringkat\PeringatanBiayaTinggi::PESAN }}</p>
                    @endif
                </div>
                <div @class(['mt-3 grid gap-2', 'grid-cols-2' => $bisaPutus, 'grid-cols-1' => ! $bisaPutus])>
                    <a href="{{ route('peringkat.show', [$periode, $b['aset']]) }}" class="inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-sky-50 text-xs font-bold text-sky-700"><x-heroicon-o-eye class="h-4 w-4" /> Detail</a>
                    @if ($bisaPutus)
                    <a href="{{ route('keputusan.edit', [$periode, $b['aset']]) }}" class="inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-emerald-50 text-xs font-bold text-emerald-700"><x-heroicon-o-check-badge class="h-4 w-4" /> {{ $b['keputusan'] ? 'Ubah Keputusan' : 'Putuskan' }}</a>
                    @endif
                </div>
            </article>
            @empty
            <x-ui.empty-state icon="funnel" title="Tidak ada aset yang cocok" text="Ubah filter atau kata kunci pencarian." />
            @endforelse
        </div>

        @if ($baris->isNotEmpty())
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[1000px] table-fixed text-left text-sm">
                <colgroup><col class="w-20"><col><col class="w-56"><col class="w-28"><col class="w-36"><col class="w-36"><col class="{{ $bisaPutus ? 'w-28' : 'w-16' }}"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3 text-center">Peringkat</th><th class="px-3 py-3">Aset</th><th class="px-3 py-3">Skor relatif</th><th class="px-3 py-3 text-right">Yi</th><th class="px-3 py-3">Rekomendasi</th><th class="px-3 py-3">Keputusan</th><th class="px-3 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($baris as $b)
                    <tr class="hover:bg-zinc-50/80">
                        <td class="px-3 py-3"><x-ui.rank :ranking="$b['hasil']->ranking" class="mx-auto" /></td>
                        <td class="px-3 py-3">
                            <p class="truncate font-bold text-zinc-800" title="{{ $b['aset']->nama_barang }}">{{ $b['aset']->nama_barang }}</p>
                            @if ($b['peringatan'])
                            <p class="mt-0.5 flex items-center gap-1 truncate text-[11px] font-semibold text-amber-700" title="{{ \App\Services\Peringkat\PeringatanBiayaTinggi::PESAN }}"><x-heroicon-s-exclamation-triangle class="h-3.5 w-3.5 shrink-0" /> Biaya tinggi — pertimbangkan hapus</p>
                            @else
                            <p class="mt-0.5 truncate text-[11px] text-zinc-500">{{ $b['aset']->kategori?->nama }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-3"><x-ui.skor :value="$b['hasil']->skor_relatif" :tindakan="$b['hasil']->rekomendasi" /></td>
                        <td class="whitespace-nowrap px-3 py-3 text-right font-mono text-xs tabular-nums text-zinc-700">{{ number_format($b['hasil']->yi, 4, ',', '.') }}</td>
                        <td class="px-3 py-3"><x-ui.badge-tindakan :tindakan="$b['hasil']->rekomendasi" /></td>
                        <td class="px-3 py-3">
                            @if ($b['keputusan'])
                            <div class="flex items-center gap-1"><x-ui.badge-tindakan :tindakan="$b['keputusan']->tindakan" />@if ($b['keputusan']->tindakan !== $b['hasil']->rekomendasi)<x-heroicon-s-arrows-right-left class="h-3.5 w-3.5 text-violet-500" title="Berbeda dari rekomendasi" />@endif</div>
                            @else
                            <x-ui.badge tone="ghost">Belum</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-1.5">
                                <x-ui.table-action :href="route('peringkat.show', [$periode, $b['aset']])" tone="view" icon="eye" label="Detail aset" />
                                @if ($bisaPutus)
                                <x-ui.table-action :href="route('keputusan.edit', [$periode, $b['aset']])" tone="success" icon="check-badge" :label="$b['keputusan'] ? 'Ubah keputusan' : 'Putuskan'" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
    @endif
</x-layouts.app>
