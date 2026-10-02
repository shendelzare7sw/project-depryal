<x-layouts.app title="Kriteria Penilaian" subtitle="Bobot & rubrik skala kriteria metode MOORA">
    @php
        $operator = auth()->user()->isOperator();
        $pas = abs($totalBobot - 100) < 0.0001;
        $aktif = $kriteria->where('is_active', true);
    @endphp

    {{-- Ringkasan bobot --}}
    <section @class(['relative min-w-0 overflow-hidden rounded-2xl border p-4 sm:p-5', 'border-emerald-200 bg-emerald-50/60' => $pas, 'border-rose-200 bg-rose-50/60' => ! $pas])>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            <div class="flex min-w-0 items-center gap-3 lg:w-80">
                <span @class(['flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-white', 'bg-emerald-600' => $pas, 'bg-rose-600' => ! $pas])>
                    @if ($pas)<x-heroicon-o-check-badge class="h-6 w-6" />@else<x-heroicon-o-exclamation-triangle class="h-6 w-6" />@endif
                </span>
                <div class="min-w-0">
                    <p class="text-2xl font-extrabold tabular-nums text-zinc-900">{{ $totalBobot }}%</p>
                    <p @class(['text-xs font-semibold', 'text-emerald-700' => $pas, 'text-rose-700' => ! $pas])>{{ $pas ? 'Total bobot sudah 100% — siap dihitung' : 'Total bobot harus tepat 100% sebelum perhitungan MOORA' }}</p>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <x-ui.progress-meter :value="$totalBobot" :max="100" label="Total bobot kriteria aktif (%)" strict />
            </div>
            <div class="grid grid-cols-3 gap-2 lg:w-80">
                <div class="rounded-xl bg-white p-2.5 text-center ring-1 ring-zinc-200/80"><p class="text-lg font-extrabold text-zinc-900">{{ $aktif->count() }}</p><p class="text-[10px] font-bold uppercase text-zinc-500">Aktif</p></div>
                <div class="rounded-xl bg-white p-2.5 text-center ring-1 ring-zinc-200/80"><p class="text-lg font-extrabold text-emerald-600">{{ $aktif->where('tipe', \App\Enums\TipeKriteria::Benefit)->count() }}</p><p class="text-[10px] font-bold uppercase text-zinc-500">Benefit</p></div>
                <div class="rounded-xl bg-white p-2.5 text-center ring-1 ring-zinc-200/80"><p class="text-lg font-extrabold text-amber-600">{{ $aktif->where('tipe', \App\Enums\TipeKriteria::Cost)->count() }}</p><p class="text-[10px] font-bold uppercase text-zinc-500">Cost</p></div>
            </div>
        </div>
    </section>

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><x-heroicon-o-scale class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Daftar Kriteria</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $kriteria->count() }} kriteria · rubrik skala tampil saat input nilai</p>
                </div>
            </div>
            @if ($operator)
            <x-ui.btn tone="primary" icon="plus" :href="route('kriteria.create')">Tambah Kriteria</x-ui.btn>
            @endif
        </header>

        @if ($kriteria->isEmpty())
        <x-ui.empty-state icon="scale" title="Belum ada kriteria" text="Tambahkan minimal 2 kriteria aktif dengan total bobot 100%." />
        @else
        <div class="divide-y divide-zinc-100 lg:hidden">
            @foreach ($kriteria as $item)
            <article class="p-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-zinc-900 font-mono text-sm font-extrabold text-white">{{ $item->kode }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-extrabold leading-snug text-zinc-900">{{ $item->nama }}</h3>
                            <span class="shrink-0 text-lg font-extrabold tabular-nums text-brand-700">{{ $item->bobotPersen() }}%</span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">@include('kriteria._status')</div>
                    </div>
                </div>
                <x-ui.rubrik-list :kriteria="$item" class="mt-3" />
                @if ($operator)
                <div class="mt-3 grid grid-cols-2 gap-2">@include('kriteria._actions', ['mobile' => true])</div>
                @endif
            </article>
            @endforeach
        </div>
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[920px] table-fixed text-left text-sm">
                <colgroup><col class="w-16"><col class="w-20"><col><col class="w-40"><col class="w-24"><col class="w-48"><col class="{{ $operator ? 'w-28' : 'w-4' }}"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3 text-center">Urutan</th><th class="px-3 py-3">Kode</th><th class="px-3 py-3">Kriteria & Rubrik</th><th class="px-3 py-3">Tipe</th><th class="px-3 py-3 text-right">Bobot</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">{{ $operator ? 'Aksi' : '' }}</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($kriteria as $item)
                    <tr class="align-top hover:bg-zinc-50/80">
                        <td class="px-3 py-3.5 text-center text-xs tabular-nums text-zinc-400">{{ $item->urutan }}</td>
                        <td class="px-3 py-3.5"><span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-zinc-900 px-2 font-mono text-xs font-extrabold text-white">{{ $item->kode }}</span></td>
                        <td class="px-3 py-3.5">
                            <p class="truncate font-bold text-zinc-800" title="{{ $item->nama }}">{{ $item->nama }}</p>
                            <x-ui.rubrik-list :kriteria="$item" class="mt-1" />
                        </td>
                        <td class="px-3 py-3.5"><x-ui.badge :tone="$item->tipe->color()">{{ $item->tipe->label() }}</x-ui.badge></td>
                        <td class="px-3 py-3.5 text-right text-base font-extrabold tabular-nums text-brand-700">{{ $item->bobotPersen() }}%</td>
                        <td class="px-3 py-3.5"><div class="flex flex-wrap gap-1.5">@include('kriteria._status')</div></td>
                        <td class="px-3 py-3.5">
                            @if ($operator)<div class="flex justify-end gap-1.5">@include('kriteria._actions', ['mobile' => false])</div>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
</x-layouts.app>
