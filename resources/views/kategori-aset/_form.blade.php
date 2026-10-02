{{-- Variabel: $kategori, $action, $method --}}
<form method="POST" action="{{ $action }}" x-data="{ busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex items-center gap-3 border-b border-zinc-100 p-4 sm:p-5">
            <a href="{{ route('kategori-aset.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
            <div class="min-w-0">
                <h2 class="text-base font-extrabold text-zinc-900">{{ $kategori->exists ? 'Perbarui kategori' : 'Kategori baru' }}</h2>
                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $kategori->exists ? $kategori->nama : 'Kode singkat dan nama kategori aset' }}</p>
            </div>
        </header>
        <div class="grid grid-cols-1 gap-4 p-4 sm:p-5 md:grid-cols-[14rem_minmax(0,1fr)]">
            <x-form.input name="kode" label="Kode" :value="$kategori->kode" required maxlength="20" placeholder="GB-KTR" />
            <x-form.input name="nama" label="Nama kategori" :value="$kategori->nama" required maxlength="100" placeholder="Gedung Kantor Pemerintah" />
        </div>
        <footer class="flex justify-end gap-2 border-t border-zinc-100 bg-zinc-50/60 px-4 py-3 sm:px-5">
            <x-ui.btn tone="white" :href="route('kategori-aset.index')">Batal</x-ui.btn>
            <x-ui.btn type="submit" tone="primary" icon="check" ::disabled="busy">Simpan</x-ui.btn>
        </footer>
    </section>
</form>
