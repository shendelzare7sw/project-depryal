<x-layouts.app title="Perbandingan Periode" subtitle="Tren peringkat aset antar periode penilaian">
    @php
        $r = $data['ringkasan'] ?? null;
        $stats = $r ? [
            ['label' => 'Peringkat naik', 'value' => $r['naik'], 'icon' => 'arrow-trending-up', 'tone' => 'emerald'],
            ['label' => 'Peringkat turun', 'value' => $r['turun'], 'icon' => 'arrow-trending-down', 'tone' => 'rose'],
            ['label' => 'Rekomendasi berubah', 'value' => $r['rekomendasi_berubah'], 'icon' => 'arrows-right-left', 'tone' => 'amber'],
            ['label' => 'Aset baru / tidak dinilai', 'value' => $r['baru'].' / '.$r['keluar'], 'icon' => 'building-office-2', 'tone' => 'sky'],
        ] : [];
        $opsi = $pilihan->mapWithKeys(fn ($p) => [$p->id => $p->nama])->all();
    @endphp

    <section class="min-w-0 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm shadow-zinc-900/[0.03] sm:p-5">
        <form method="GET" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
            <x-form.select name="lama" label="Periode pembanding (lama)" :options="$opsi" :value="$lama?->id" />
            <x-form.select name="baru" label="Periode acuan (baru)" :options="$opsi" :value="$baru?->id" />
            <x-ui.btn type="submit" tone="primary" icon="arrows-right-left" class="h-12">Bandingkan</x-ui.btn>
        </form>
        @error('lama')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
    </section>

    @if (! $data)
    <x-ui.empty-state icon="arrows-right-left" title="Belum bisa dibandingkan" text="Perbandingan membutuhkan minimal dua periode yang sudah dihitung MOORA." />
    @else
    <x-ui.stat-grid :items="$stats" />

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex items-center gap-3 border-b border-zinc-200/80 p-4 sm:p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><x-heroicon-o-chart-bar class="h-5 w-5" /></span>
            <div class="min-w-0">
                <h2 class="truncate text-base font-extrabold text-zinc-900">{{ $lama->nama }} → {{ $baru->nama }}</h2>
                <p class="mt-0.5 text-xs text-zinc-500">{{ $data['baris']->count() }} aset · diurutkan menurut peringkat periode acuan</p>
            </div>
        </header>

        <div class="divide-y divide-zinc-100 lg:hidden">
            @foreach ($data['baris'] as $b)
            <article class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $b['aset']->nama_barang }}</h3>
                        <p class="mt-1 truncate font-mono text-[11px] text-zinc-500">{{ $b['aset']->kode_barang }} · NUP {{ $b['aset']->nup }}</p>
                    </div>
                    <x-ui.tren :naik="$b['naik']" :baru="$b['lama'] === null" />
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-zinc-50 p-3 text-[11px]">
                    <div><p class="text-zinc-500">Lama</p><p class="font-bold text-zinc-800">{{ $b['lama'] ? '#'.$b['lama']->ranking.' · '.number_format($b['lama']->skor_relatif, 2, ',', '.') : '—' }}</p>@if ($b['lama'])<x-ui.badge-tindakan :tindakan="$b['lama']->rekomendasi" class="mt-1" />@endif</div>
                    <div><p class="text-zinc-500">Baru</p><p class="font-bold text-zinc-800">{{ $b['baru'] ? '#'.$b['baru']->ranking.' · '.number_format($b['baru']->skor_relatif, 2, ',', '.') : '—' }}</p>@if ($b['baru'])<x-ui.badge-tindakan :tindakan="$b['baru']->rekomendasi" class="mt-1" />@endif</div>
                </div>
            </article>
            @endforeach
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[900px] table-fixed text-left text-sm">
                <colgroup><col><col class="w-28"><col class="w-36"><col class="w-28"><col class="w-36"><col class="w-28"><col class="w-24"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3">Aset</th><th class="px-3 py-3 text-center">Peringkat lama</th><th class="px-3 py-3">Rekomendasi lama</th><th class="px-3 py-3 text-center">Peringkat baru</th><th class="px-3 py-3">Rekomendasi baru</th><th class="px-3 py-3">Tren</th><th class="px-3 py-3 text-right">Δ skor</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($data['baris'] as $b)
                    <tr @class(['hover:bg-zinc-50/80', 'bg-amber-50/40' => $b['berubah']])>
                        <td class="px-3 py-3"><p class="truncate font-bold text-zinc-800" title="{{ $b['aset']->nama_barang }}">{{ $b['aset']->nama_barang }}</p><p class="truncate font-mono text-[11px] text-zinc-500">{{ $b['aset']->kode_barang }} · NUP {{ $b['aset']->nup }}</p></td>
                        <td class="px-3 py-3 text-center font-bold tabular-nums text-zinc-700">{{ $b['lama']?->ranking ?? '—' }}</td>
                        <td class="px-3 py-3">@if ($b['lama'])<x-ui.badge-tindakan :tindakan="$b['lama']->rekomendasi" />@else<span class="text-zinc-400">—</span>@endif</td>
                        <td class="px-3 py-3 text-center font-bold tabular-nums text-zinc-900">{{ $b['baru']?->ranking ?? '—' }}</td>
                        <td class="px-3 py-3">@if ($b['baru'])<x-ui.badge-tindakan :tindakan="$b['baru']->rekomendasi" />@else<span class="text-zinc-400">—</span>@endif</td>
                        <td class="px-3 py-3"><x-ui.tren :naik="$b['naik']" :baru="$b['lama'] === null" /></td>
                        <td @class(['px-3 py-3 text-right text-xs font-bold tabular-nums', 'text-emerald-700' => ($b['selisih_skor'] ?? 0) > 0, 'text-rose-700' => ($b['selisih_skor'] ?? 0) < 0, 'text-zinc-500' => ($b['selisih_skor'] ?? 0) == 0])>{{ $b['selisih_skor'] === null ? '—' : ($b['selisih_skor'] > 0 ? '+' : '').number_format($b['selisih_skor'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif
</x-layouts.app>
