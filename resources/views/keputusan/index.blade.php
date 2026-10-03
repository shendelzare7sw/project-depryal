<x-layouts.app title="Keputusan" subtitle="Tetapkan tindakan untuk setiap aset hasil MOORA">
    @if (! $periode)
    {{-- Belum ada periode yang siap diputuskan --}}
    <x-ui.card>
        <x-ui.empty-state icon="check-badge" title="Belum ada aset yang menunggu keputusan"
            :text="$terakhir ? 'Periode terakhir “'.$terakhir->nama.'” berstatus '.$terakhir->status->label().'. Keputusan baru dapat diberikan setelah Operator menjalankan Hitung MOORA.' : 'Operator belum membuat periode penilaian.'">
            @if ($terakhir && in_array($terakhir->status->value, ['final'], true))
            <x-ui.btn tone="white" icon="chart-bar" :href="route('peringkat.index', $terakhir)">Lihat Hasil Periode Final</x-ui.btn>
            @endif
        </x-ui.empty-state>
        <ol class="mx-auto grid max-w-3xl gap-2 pb-4 text-left sm:grid-cols-3">
            @foreach ([['1', 'Operator', 'Mengisi data aset, kriteria, dan nilai setiap aset dalam periode.'], ['2', 'Operator', 'Menjalankan Hitung MOORA — Anda menerima notifikasi.'], ['3', 'Pimpinan', 'Menetapkan tindakan tiap aset di halaman ini, lalu finalisasi.']] as [$no, $siapa, $ket])
            <li class="flex gap-3 rounded-xl bg-zinc-50 p-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-700 text-xs font-extrabold text-white">{{ $no }}</span>
                <span class="text-xs leading-5 text-zinc-600"><strong class="block text-zinc-900">{{ $siapa }}</strong>{{ $ket }}</span>
            </li>
            @endforeach
        </ol>
    </x-ui.card>
    @else
    @php
        $belum = $baris->filter(fn ($b) => $b['keputusan'] === null)->values();
        $sudah = $baris->filter(fn ($b) => $b['keputusan'] !== null)->values();
    @endphp
    <x-ui.hero :eyebrow="'Periode · '.$periode->nama" :title="$belum->isNotEmpty() ? $belum->count().' aset menunggu keputusan Anda' : 'Semua aset sudah diputuskan'"
        :subtitle="$belum->isNotEmpty() ? 'Urut dari peringkat teratas. Rekomendasi sistem hanya alat bantu — keputusan akhir di tangan Anda.' : 'Tinjau kembali bila perlu, lalu finalisasi periode untuk mengunci hasil.'">
        <x-slot:actions>
            @if ($belumPertama)
            <x-ui.btn tone="amber" icon="check-badge" :href="route('keputusan.edit', [$periode, $belumPertama])">Mulai Putuskan</x-ui.btn>
            @endif
            @if ($bisaFinalisasi)
            <x-confirm-form :action="route('periode.finalisasi', $periode)" title="Finalisasi periode?"
                text="Semua nilai, hasil, dan keputusan akan dikunci. Status aset diperbarui sesuai keputusan." confirm="Ya, finalisasi" icon="warning">
                <x-ui.btn type="submit" tone="amber" icon="lock-closed">Finalisasi Periode</x-ui.btn>
            </x-confirm-form>
            @endif
            <x-ui.btn tone="glass" icon="chart-bar" :href="route('peringkat.index', $periode)">Lihat Peringkat Lengkap</x-ui.btn>
        </x-slot:actions>
        <x-slot:aside>
            <div class="w-full rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/20 lg:w-64">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-100">Progres keputusan</p>
                <p class="mt-1 text-3xl font-extrabold !text-white">{{ $diputuskan }}<span class="text-base font-bold text-brand-100">/{{ $total }}</span></p>
                <progress class="progress mt-3 h-2 w-full bg-white/20 [&::-webkit-progress-value]:bg-amber-300 [&::-moz-progress-bar]:bg-amber-300" value="{{ $diputuskan }}" max="{{ max(1, $total) }}"></progress>
            </div>
        </x-slot:aside>
    </x-ui.hero>

    @foreach ([['Menunggu keputusan', 'clock', 'text-amber-500', $belum, 'Semua aset sudah diputuskan.'], ['Sudah diputuskan', 'check-circle', 'text-emerald-600', $sudah, 'Belum ada keputusan.']] as [$judul, $ikon, $nada, $daftar, $kosong])
    <x-ui.card :title="$judul" :icon="$ikon" :icon-tone="$nada" flush>
        <x-slot:chip><x-ui.badge>{{ $daftar->count() }}</x-ui.badge></x-slot:chip>
        <div class="divide-y divide-zinc-100">
            @forelse ($daftar as $b)
            <a href="{{ route('keputusan.edit', [$periode, $b['aset']]) }}" class="group flex items-center gap-3 px-4 py-3 transition hover:bg-zinc-50 sm:px-5">
                <x-ui.rank :ranking="$b['hasil']->ranking" />
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold text-zinc-900 group-hover:text-brand-700">{{ $b['aset']->nama_barang }}</span>
                    <span class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-zinc-500">
                        Skor {{ number_format($b['hasil']->skor_relatif, 2, ',', '.') }} · rekomendasi <x-ui.badge-tindakan :tindakan="$b['hasil']->rekomendasi" />
                        @if ($b['keputusan']) → keputusan <x-ui.badge-tindakan :tindakan="$b['keputusan']->tindakan" />@endif
                        @if ($b['peringatan'])<span class="font-semibold text-amber-700">⚠ biaya tinggi</span>@endif
                    </span>
                </span>
                <x-heroicon-m-chevron-right class="h-4 w-4 shrink-0 text-zinc-300 group-hover:text-brand-600" />
            </a>
            @empty
            <p class="px-5 py-6 text-xs text-zinc-500">{{ $kosong }}</p>
            @endforelse
        </div>
    </x-ui.card>
    @endforeach
    @endif
</x-layouts.app>
