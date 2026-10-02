<x-layouts.app title="Ubah Periode" :subtitle="$periode->nama">
    <form method="POST" action="{{ route('periode.update', $periode) }}" x-data="{ busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
        @csrf
        @method('PUT')
        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
            <header class="flex items-center gap-3 border-b border-zinc-100 p-4 sm:p-5">
                <a href="{{ route('periode.show', $periode) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Perbarui identitas periode</h2>
                    <p class="mt-0.5 truncate text-xs text-zinc-500">Daftar aset tidak berubah · status {{ $periode->status->label() }}</p>
                </div>
            </header>
            <div class="grid grid-cols-1 gap-4 p-4 sm:p-5 lg:grid-cols-[minmax(0,1fr)_14rem_14rem]">
                <x-form.input name="nama" label="Nama periode" :value="$periode->nama" required maxlength="150" />
                <x-form.input name="tanggal_mulai" label="Tanggal mulai" type="date" :value="$periode->tanggal_mulai?->format('Y-m-d')" required />
                <x-form.input name="tanggal_selesai" label="Tanggal selesai" type="date" :value="$periode->tanggal_selesai?->format('Y-m-d')" />
            </div>
            <footer class="flex justify-end gap-2 border-t border-zinc-100 bg-zinc-50/60 px-4 py-3 sm:px-5">
                <x-ui.btn tone="white" :href="route('periode.show', $periode)">Batal</x-ui.btn>
                <x-ui.btn type="submit" tone="primary" icon="check" ::disabled="busy">Simpan</x-ui.btn>
            </footer>
        </section>
    </form>
</x-layouts.app>
