<x-layouts.app title="Periode Penilaian" subtitle="Siklus penilaian: draft → dinilai → dihitung → final">
    @php($operator = auth()->user()->isOperator())

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><x-heroicon-o-calendar-days class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Daftar Periode</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $periode->total() }} periode · hanya satu periode aktif (belum final)</p>
                </div>
            </div>
            @if ($operator)
                @if ($adaAktif)
                <span class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">
                    <x-heroicon-o-information-circle class="h-4 w-4 shrink-0" /> Selesaikan periode aktif sebelum membuat yang baru
                </span>
                @else
                <x-ui.btn tone="primary" icon="plus" :href="route('periode.create')">Buat Periode</x-ui.btn>
                @endif
            @endif
        </header>

        @if ($periode->isEmpty())
        <x-ui.empty-state icon="calendar-days" title="Belum ada periode penilaian"
            :text="$operator ? 'Buat periode, pilih aset yang dinilai, lalu isi nilai setiap kriteria.' : 'Operator belum membuat periode penilaian.'">
            @if ($operator)<x-ui.btn tone="primary" icon="plus" :href="route('periode.create')">Buat Periode Pertama</x-ui.btn>@endif
        </x-ui.empty-state>
        @else
        <div class="divide-y divide-zinc-100 lg:hidden">
            @foreach ($periode as $item)
            <a href="{{ route('periode.show', $item) }}" id="m-{{ $loop->iteration }}" class="block scroll-mt-24 p-4 transition active:bg-zinc-50">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $item->nama }}</h3>
                        <p class="mt-1 text-[11px] text-zinc-500">{{ $item->tanggal_mulai?->translatedFormat('d M Y') }}{{ $item->tanggal_selesai ? ' – '.$item->tanggal_selesai->translatedFormat('d M Y') : '' }}</p>
                    </div>
                    <x-ui.badge-status :status="$item->status" />
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-zinc-50 p-3 text-[11px]">
                    <div><p class="text-zinc-400">Jumlah aset</p><p class="mt-0.5 font-semibold text-zinc-700">{{ $item->aset_count }} aset</p></div>
                    <div><p class="text-zinc-400">Dihitung</p><p class="mt-0.5 font-semibold text-zinc-700">{{ $item->dihitung_pada?->translatedFormat('d M Y H:i') ?? '—' }}</p></div>
                </div>
            </a>
            @endforeach
        </div>
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[900px] table-fixed text-left text-sm">
                <colgroup><col class="w-12"><col><col class="w-56"><col class="w-24"><col class="w-36"><col class="w-44"><col class="w-28"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3 text-center">No</th><th class="px-3 py-3">Nama Periode</th><th class="px-3 py-3">Tanggal</th><th class="px-3 py-3 text-center">Aset</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Dihitung</th><th class="px-3 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($periode as $i => $item)
                    <tr id="d-{{ $loop->iteration }}" class="scroll-mt-24 hover:bg-zinc-50/80">
                        <td class="px-3 py-3 text-center text-xs tabular-nums text-zinc-400">{{ $periode->firstItem() + $i }}</td>
                        <td class="px-3 py-3"><p class="truncate font-bold text-zinc-800" title="{{ $item->nama }}">{{ $item->nama }}</p></td>
                        <td class="whitespace-nowrap px-3 py-3 text-xs text-zinc-600">{{ $item->tanggal_mulai?->translatedFormat('d M Y') }}{{ $item->tanggal_selesai ? ' – '.$item->tanggal_selesai->translatedFormat('d M Y') : '' }}</td>
                        <td class="px-3 py-3 text-center text-xs font-bold tabular-nums">{{ $item->aset_count }}</td>
                        <td class="px-3 py-3"><x-ui.badge-status :status="$item->status" /></td>
                        <td class="whitespace-nowrap px-3 py-3 text-xs text-zinc-600">{{ $item->dihitung_pada?->translatedFormat('d M Y H:i') ?? '—' }}</td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-1.5">
                                <x-ui.table-action :href="route('periode.show', $item)" tone="view" icon="eye" label="Buka periode" />
                                @if ($operator && ! $item->isFinal())
                                <x-ui.table-action :href="route('periode.edit', $item)" tone="edit" icon="pencil-square" label="Ubah periode" />
                                @endif
                                @if (in_array($item->status->value, ['dihitung', 'final'], true))
                                <x-ui.table-action :href="route('peringkat.index', $item)" tone="success" icon="chart-bar" label="Lihat peringkat" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <x-ui.muat-lagi :items="$periode" :langkah="10" />
    </section>
</x-layouts.app>
