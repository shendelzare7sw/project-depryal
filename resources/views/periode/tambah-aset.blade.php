<x-layouts.app title="Tambah Aset ke Periode" :subtitle="$periode->nama">
    <form method="POST" action="{{ route('periode.aset.store', $periode) }}" x-data="{ q: '', busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
        @csrf
        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
            <header class="flex items-center gap-3 p-4 sm:p-5">
                <a href="{{ route('periode.show', $periode) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Pilih aset tambahan</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $aset->count() }} aset aktif belum termasuk periode ini</p>
                </div>
            </header>
            @if ($periode->status->value === 'dihitung')
            <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900 sm:mx-5">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-600" />
                <p>Periode sudah dihitung. <strong>Menambah aset akan menghapus hasil MOORA dan keputusan pimpinan</strong> — perhitungan harus dijalankan ulang setelah nilai aset baru diisi.</p>
            </div>
            @endif
            @error('aset_ids')
            <p class="mx-4 mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-700 sm:mx-5">{{ $message }}</p>
            @enderror
        </section>

        <x-ui.card title="Aset aktif" icon="building-office-2" subtitle="Centang aset yang akan ikut dinilai pada periode ini">
            @if ($aset->isEmpty())
            <x-ui.empty-state icon="check-circle" title="Semua aset aktif sudah termasuk" text="Tambahkan data aset baru terlebih dahulu bila ada aset yang belum terdaftar." />
            @else
            <label class="relative block">
                <span class="sr-only">Cari aset</span>
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                <input type="search" x-model="q" placeholder="Cari aset…" class="h-10 w-full rounded-xl border border-zinc-200 pl-10 pr-3 text-xs outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
            </label>
            <div class="divide-y divide-zinc-100 rounded-xl border border-zinc-200">
                @foreach ($aset as $item)
                <label class="flex min-h-[3.25rem] cursor-pointer items-center gap-3 px-3 py-2.5 hover:bg-zinc-50" x-show="!q || @js(mb_strtolower($item->nama_barang.' '.$item->kode_barang)).includes(q.toLowerCase())">
                    <input type="checkbox" name="aset_ids[]" value="{{ $item->id }}" class="checkbox checkbox-sm checkbox-primary" @checked(in_array($item->id, old('aset_ids', [])))>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-zinc-800">{{ $item->nama_barang }}</span>
                        <span class="block truncate font-mono text-[11px] text-zinc-500">{{ $item->kode_barang }} · NUP {{ $item->nup }} · {{ $item->kategori?->nama }}</span>
                    </span>
                    <x-ui.badge-perhatian :aset="$item" />
                </label>
                @endforeach
            </div>
            @endif
        </x-ui.card>

        <x-ui.action-bar>
            <div class="flex items-center justify-end gap-2">
                <x-ui.btn tone="white" :href="route('periode.show', $periode)">Batal</x-ui.btn>
                <x-ui.btn type="submit" tone="primary" icon="plus" class="flex-1 sm:flex-none" ::disabled="busy" :disabled="$aset->isEmpty()">Tambahkan ke Periode</x-ui.btn>
            </div>
        </x-ui.action-bar>
    </form>
</x-layouts.app>
