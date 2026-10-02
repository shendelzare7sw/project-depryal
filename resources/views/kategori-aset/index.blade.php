<x-layouts.app title="Kategori Aset" subtitle="Pengelompokan gedung & bangunan BMD">
    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><x-heroicon-o-tag class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Daftar Kategori</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $kategori->count() }} kategori · {{ $kategori->sum('aset_count') }} aset terhubung</p>
                </div>
            </div>
            <x-ui.btn tone="primary" icon="plus" :href="route('kategori-aset.create')">Tambah Kategori</x-ui.btn>
        </header>

        @if ($kategori->isEmpty())
        <x-ui.empty-state icon="tag" title="Belum ada kategori" text="Tambahkan kategori, atau biarkan import BMD membuatnya otomatis." />
        @else
        <div class="divide-y divide-zinc-100 lg:hidden">
            @foreach ($kategori as $item)
            <article class="flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-violet-50 text-violet-700">
                    <span class="text-sm font-extrabold leading-none">{{ $item->aset_count }}</span><span class="text-[9px] font-bold uppercase">aset</span>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $item->nama }}</h3>
                    <p class="mt-0.5 font-mono text-[11px] text-zinc-500">{{ $item->kode }}</p>
                </div>
                <div class="flex shrink-0 gap-1.5">@include('kategori-aset._actions')</div>
            </article>
            @endforeach
        </div>
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full table-fixed text-left text-sm">
                <colgroup><col class="w-12"><col class="w-40"><col><col class="w-36"><col class="w-28"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3 text-center">No</th><th class="px-3 py-3">Kode</th><th class="px-3 py-3">Nama Kategori</th><th class="px-3 py-3 text-center">Jumlah Aset</th><th class="px-3 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($kategori as $item)
                    <tr class="hover:bg-zinc-50/80">
                        <td class="px-3 py-3 text-center text-xs tabular-nums text-zinc-400">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap px-3 py-3 font-mono text-xs font-semibold text-zinc-700">{{ $item->kode }}</td>
                        <td class="px-3 py-3"><p class="truncate font-bold text-zinc-800">{{ $item->nama }}</p></td>
                        <td class="px-3 py-3 text-center"><x-ui.badge :tone="$item->aset_count > 0 ? 'accent' : 'ghost'">{{ $item->aset_count }} aset</x-ui.badge></td>
                        <td class="px-3 py-3"><div class="flex justify-end gap-1.5">@include('kategori-aset._actions')</div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
</x-layouts.app>
