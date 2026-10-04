<x-layouts.app title="Laporan" subtitle="Cetak PDF & Excel hasil penilaian, beserta riwayat cetak">
    @php
        $bisaBuat = ! auth()->user()->isAdmin();
        $opsiPeriode = [];
        foreach ($periode as $p) {
            $opsiPeriode[$p->id] = $p->nama.' · '.$p->status->label();
        }
    @endphp
    @if (session('unduh'))<div x-data x-init="window.location.href = @js(session('unduh'))"></div>@endif

    <div @class(['grid min-w-0 gap-5', 'xl:grid-cols-[24rem_minmax(0,1fr)]' => $bisaBuat])>
        @if ($bisaBuat)
        <x-ui.card title="Buat laporan" icon="document-plus" subtitle="Hanya periode yang sudah dihitung / final">
            @if ($periode->isEmpty())
            <x-ui.empty-state icon="calculator" title="Belum ada periode yang dihitung" text="Laporan tersedia setelah Hitung MOORA dijalankan." />
            @else
            <form method="POST" action="{{ route('laporan.store') }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
                @csrf
                <x-form.select name="periode_id" label="Periode" :options="$opsiPeriode" :value="$periode->first()?->id" required />
                <fieldset>
                    <legend class="mb-1.5 text-sm font-semibold text-zinc-700">Jenis laporan</legend>
                    <div class="space-y-2">
                        @foreach (\App\Enums\JenisLaporan::cases() as $j)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                            <input type="radio" name="jenis" value="{{ $j->value }}" class="radio radio-sm radio-primary mt-0.5" @checked(old('jenis', 'lengkap') === $j->value) required>
                            <span class="text-sm font-semibold text-zinc-800">{{ $j->label() }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('jenis')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </fieldset>
                <fieldset>
                    <legend class="mb-1.5 text-sm font-semibold text-zinc-700">Format</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (\App\Enums\FormatLaporan::cases() as $f)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                            <input type="radio" name="format" value="{{ $f->value }}" class="radio radio-sm radio-primary" @checked(old('format', 'pdf') === $f->value) required>
                            <span class="text-xs font-bold text-zinc-800">{{ $f->label() }}</span>
                        </label>
                        @endforeach
                    </div>
                </fieldset>
                <x-ui.btn type="submit" tone="primary" icon="arrow-down-tray" class="w-full" ::disabled="busy">
                    <span x-text="busy ? 'Menyiapkan berkas…' : 'Buat & Unduh'">Buat &amp; Unduh</span>
                </x-ui.btn>
            </form>
            @endif
        </x-ui.card>
        @endif

        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
            <header class="flex items-center gap-3 border-b border-zinc-200/80 p-4 sm:p-5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><x-heroicon-o-clock class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Riwayat cetak</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $laporan->total() }} berkas · dapat diunduh ulang</p>
                </div>
            </header>

            <div class="divide-y divide-zinc-100 lg:hidden">
                @forelse ($laporan as $l)
                <article id="m-{{ $loop->iteration }}" class="flex scroll-mt-24 items-center gap-3 p-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-[10px] font-extrabold uppercase {{ $l->format->value === 'pdf' ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $l->format->value }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-zinc-900">{{ $l->jenis->label() }}</p>
                        <p class="truncate text-[11px] text-zinc-500">{{ $l->periode?->nama ?? '—' }} · {{ $l->user?->name }} · {{ $l->created_at?->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    <x-ui.table-action :href="route('laporan.download', $l)" tone="success" icon="arrow-down-tray" label="Unduh" />
                </article>
                @empty
                <x-ui.empty-state icon="document-chart-bar" title="Belum ada laporan" text="Laporan yang dibuat akan tercatat di sini." />
                @endforelse
            </div>

            @if ($laporan->isNotEmpty())
            <div class="hidden overflow-x-auto lg:block">
                <table class="w-full min-w-[820px] table-fixed text-left text-sm">
                    <colgroup><col class="w-20"><col><col class="w-[24%]"><col class="w-40"><col class="w-40"><col class="w-20"></colgroup>
                    <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                        <tr><th class="px-3 py-3">Format</th><th class="px-3 py-3">Jenis</th><th class="px-3 py-3">Periode</th><th class="px-3 py-3">Dibuat oleh</th><th class="px-3 py-3">Waktu</th><th class="px-3 py-3 text-right">Unduh</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($laporan as $l)
                        <tr id="d-{{ $loop->iteration }}" class="scroll-mt-24 hover:bg-zinc-50/80">
                            <td class="px-3 py-3"><x-ui.badge :tone="$l->format->color()">{{ strtoupper($l->format->value) }}</x-ui.badge></td>
                            <td class="px-3 py-3"><p class="truncate font-bold text-zinc-800" title="{{ $l->nama_file }}">{{ $l->jenis->label() }}</p></td>
                            <td class="px-3 py-3 text-xs text-zinc-600"><p class="truncate">{{ $l->periode?->nama ?? '—' }}</p></td>
                            <td class="px-3 py-3 text-xs text-zinc-600"><p class="truncate">{{ $l->user?->name }}</p></td>
                            <td class="whitespace-nowrap px-3 py-3 text-xs text-zinc-600">{{ $l->created_at?->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-3 py-3"><div class="flex justify-end"><x-ui.table-action :href="route('laporan.download', $l)" tone="success" icon="arrow-down-tray" label="Unduh {{ $l->nama_file }}" /></div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            <x-ui.muat-lagi :items="$laporan" />
        </section>
    </div>
</x-layouts.app>
