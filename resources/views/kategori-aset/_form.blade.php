{{-- Variabel: $kategori, $action, $method --}}
<form method="POST" action="{{ $action }}" x-data="{ busy: false }" @submit="busy = true">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <x-ui.card>
        <div class="grid grid-cols-1 md:grid-cols-[12rem_1fr] gap-x-4 gap-y-2">
            <x-form.input name="kode" label="Kode" :value="$kategori->kode" required maxlength="20" placeholder="GB-KTR" />
            <x-form.input name="nama" label="Nama Kategori" :value="$kategori->nama" required maxlength="100" />
        </div>
    </x-ui.card>
    <div class="flex gap-2 justify-end mt-4">
        <a href="{{ route('kategori-aset.index') }}" class="btn btn-ghost min-h-[44px]">Batal</a>
        <button type="submit" class="btn btn-primary min-h-[44px] flex-1 md:flex-none" :disabled="busy">
            <span class="loading loading-spinner loading-sm" x-show="busy"></span> Simpan
        </button>
    </div>
</form>
