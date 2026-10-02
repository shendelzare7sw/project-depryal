<x-layouts.app title="Data Aset BMD" subtitle="Barang Milik Daerah — gedung & bangunan Kecamatan Batuceper">
    @php
        $operator = auth()->user()->isOperator();
        $adaFilter = request()->hasAny(['q', 'kategori', 'status']) && request()->collect()->only(['q', 'kategori', 'status'])->filter()->isNotEmpty();
    @endphp

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="border-b border-zinc-200/80 p-4 sm:p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <x-heroicon-o-building-office-2 class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-zinc-900">Daftar Aset</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">{{ $aset->total() }} aset {{ $adaFilter ? 'sesuai filter' : 'tercatat' }}</p>
                    </div>
                </div>
                @if ($operator)
                <div class="grid grid-cols-3 gap-2 sm:flex">
                    <x-ui.btn tone="soft-sky" icon="arrow-down-tray" :href="route('aset.export')" compact title="Export Excel">Export</x-ui.btn>
                    <x-ui.btn tone="soft-emerald" icon="arrow-up-tray" :href="route('aset.import')" compact title="Import BMD">Import BMD</x-ui.btn>
                    <x-ui.btn tone="primary" icon="plus" :href="route('aset.create')" compact title="Tambah aset">Tambah Aset</x-ui.btn>
                </div>
                @endif
            </div>

            <form method="GET" action="{{ route('aset.index') }}" class="mt-4 grid grid-cols-2 gap-2 xl:grid-cols-[minmax(220px,1fr)_minmax(180px,0.45fr)_minmax(160px,0.35fr)_auto]">
                <label class="relative col-span-2 xl:col-span-1">
                    <span class="sr-only">Cari aset</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode barang…"
                        class="h-10 w-full rounded-xl border border-zinc-200 bg-white pl-10 pr-3 text-xs outline-none placeholder:text-zinc-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </label>
                <select name="kategori" aria-label="Filter kategori" class="h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $id => $nama)
                    <option value="{{ $id }}" @selected((string) request('kategori') === (string) $id)>{{ $nama }}</option>
                    @endforeach
                </select>
                <select name="status" aria-label="Filter status" class="h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Semua status</option>
                    @foreach (\App\Enums\StatusAset::options() as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="col-span-2 flex gap-2 xl:col-span-1">
                    <x-ui.btn type="submit" tone="dark" icon="funnel" class="flex-1">Filter</x-ui.btn>
                    @if ($adaFilter)
                    <x-ui.btn tone="soft-rose" icon="x-mark" :href="route('aset.index')" title="Hapus filter" aria-label="Hapus filter"></x-ui.btn>
                    @endif
                </div>
            </form>
        </header>

        {{-- Ponsel & tablet: kartu --}}
        <div class="divide-y divide-zinc-100 lg:hidden">
            @forelse ($aset as $item)
            <article class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $item->nama_barang }}</h3>
                        <p class="mt-1 truncate font-mono text-[11px] text-zinc-500">{{ $item->kode_barang }} · NUP {{ $item->nup }}</p>
                    </div>
                    <x-ui.badge-status :status="$item->status" />
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-zinc-50 p-3 text-[11px]">
                    <div class="min-w-0">
                        <p class="text-zinc-400">Kategori</p>
                        <p class="mt-0.5 truncate font-semibold text-zinc-700">{{ $item->kategori?->nama ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-zinc-400">Nilai buku</p>
                        <p class="mt-0.5 truncate font-semibold text-zinc-700"><x-rupiah :value="$item->nilai_buku" /></p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-zinc-400">Sisa UEB</p>
                        <p @class(['mt-0.5 font-semibold', 'text-amber-700' => $item->perlu_perhatian, 'text-zinc-700' => ! $item->perlu_perhatian])>{{ $item->sisa_ueb }} dari {{ $item->umur_ekonomis }} tahun</p>
                    </div>
                    <div class="min-w-0 self-end">
                        <x-ui.badge-perhatian :aset="$item" />
                    </div>
                </div>
                <div @class(['mt-3 grid gap-2', 'grid-cols-3' => $operator, 'grid-cols-1' => ! $operator])>
                    <a href="{{ route('aset.show', $item) }}" class="inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-sky-50 text-xs font-bold text-sky-700"><x-heroicon-o-eye class="h-4 w-4" /> Detail</a>
                    @if ($operator)
                    <a href="{{ route('aset.edit', $item) }}" class="inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-amber-50 text-xs font-bold text-amber-700"><x-heroicon-o-pencil-square class="h-4 w-4" /> Ubah</a>
                    <x-confirm-form :action="route('aset.destroy', $item)" method="DELETE" title="Hapus aset?" text="{{ $item->nama_barang }} akan dihapus dari daftar." confirm="Ya, hapus">
                        <button type="submit" class="inline-flex h-9 w-full items-center justify-center gap-1 rounded-lg bg-rose-50 text-xs font-bold text-rose-700"><x-heroicon-o-trash class="h-4 w-4" /> Hapus</button>
                    </x-confirm-form>
                    @endif
                </div>
            </article>
            @empty
            <x-ui.empty-state icon="building-office-2" :title="$adaFilter ? 'Tidak ada aset yang cocok' : 'Belum ada data aset'"
                :text="$adaFilter ? 'Ubah kata kunci atau filter pencarian.' : 'Tambahkan aset secara manual atau import dari Excel BMD.'" />
            @endforelse
        </div>

        {{-- Desktop: tabel --}}
        @if ($aset->isNotEmpty())
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[980px] table-fixed text-left text-sm">
                <colgroup>
                    <col class="w-12"><col class="w-40"><col><col class="w-[15%]"><col class="w-32"><col class="w-20"><col class="w-32"><col class="{{ $operator ? 'w-32' : 'w-16' }}">
                </colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr>
                        <th class="px-3 py-3 text-center">No</th>
                        <th class="px-3 py-3">Kode / NUP</th>
                        <th class="px-3 py-3">Nama Barang</th>
                        <th class="px-3 py-3">Kategori</th>
                        <th class="px-3 py-3 text-right">Nilai Buku</th>
                        <th class="px-3 py-3 text-center">Sisa UEB</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($aset as $i => $item)
                    <tr class="hover:bg-zinc-50/80">
                        <td class="px-3 py-3 text-center text-xs tabular-nums text-zinc-400">{{ $aset->firstItem() + $i }}</td>
                        <td class="px-3 py-3"><p class="truncate font-mono text-xs font-semibold text-zinc-700" title="{{ $item->kode_barang }}">{{ $item->kode_barang }}</p><p class="text-[11px] text-zinc-400">NUP {{ $item->nup }}</p></td>
                        <td class="px-3 py-3">
                            <p class="truncate font-bold text-zinc-800" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</p>
                            @if ($item->perlu_perhatian)<x-ui.badge-perhatian :aset="$item" class="mt-1" />@else<p class="mt-0.5 truncate text-[11px] text-zinc-500">{{ $item->lokasi ?? '—' }}</p>@endif
                        </td>
                        <td class="px-3 py-3 text-xs text-zinc-600"><p class="truncate" title="{{ $item->kategori?->nama }}">{{ $item->kategori?->nama ?? '-' }}</p></td>
                        <td class="whitespace-nowrap px-3 py-3 text-right text-xs font-semibold tabular-nums text-zinc-800"><x-rupiah :value="$item->nilai_buku" /></td>
                        <td @class(['whitespace-nowrap px-3 py-3 text-center text-xs font-bold tabular-nums', 'text-amber-700' => $item->perlu_perhatian, 'text-zinc-700' => ! $item->perlu_perhatian])>{{ $item->sisa_ueb }} th</td>
                        <td class="px-3 py-3"><x-ui.badge-status :status="$item->status" /></td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-1.5">
                                <x-ui.table-action :href="route('aset.show', $item)" tone="view" icon="eye" label="Detail aset" />
                                @if ($operator)
                                <x-ui.table-action :href="route('aset.edit', $item)" tone="edit" icon="pencil-square" label="Ubah aset" />
                                <x-confirm-form :action="route('aset.destroy', $item)" method="DELETE" title="Hapus aset?" text="{{ $item->nama_barang }} akan dihapus dari daftar." confirm="Ya, hapus">
                                    <x-ui.table-action type="submit" tone="delete" icon="trash" label="Hapus aset" />
                                </x-confirm-form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if ($aset->hasPages())
        <footer class="border-t border-zinc-200/80 px-4 py-3">{{ $aset->links() }}</footer>
        @endif
    </section>
</x-layouts.app>
