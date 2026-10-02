<x-layouts.app title="Import Data BMD">
    <x-ui.page-header title="Import Data BMD" subtitle="Unggah Excel → periksa pratinjau → simpan">
        <x-slot:actions>
            <a href="{{ route('aset.index') }}" class="btn btn-outline btn-sm gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-ui.card title="1. Unggah berkas Excel">
            <form method="POST" action="{{ route('aset.import.store') }}" enctype="multipart/form-data"
                x-data="{ busy: false }" @submit="busy = true" class="space-y-3">
                @csrf
                <x-form.file name="file" label="Berkas Excel BMD (.xlsx / .xls)" accept=".xlsx,.xls" required />
                <button type="submit" class="btn btn-primary w-full min-h-[44px]" :disabled="busy">
                    <span class="loading loading-spinner loading-sm" x-show="busy"></span>
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4" x-show="!busy" /> Unggah &amp; Pratinjau
                </button>
            </form>
            <p class="text-xs text-base-content/60">Data belum disimpan pada langkah ini. Anda akan melihat pratinjau baris valid/error terlebih dahulu.</p>
        </x-ui.card>

        <x-ui.card title="Format kolom">
            <p class="text-sm">Gunakan template agar judul kolom sesuai. Baris judul boleh berada di 10 baris teratas.</p>
            <a href="{{ route('aset.import.template') }}" class="btn btn-outline btn-sm gap-1 w-full md:w-auto">
                <x-heroicon-o-document-arrow-down class="w-4 h-4" /> Unduh Template Excel
            </a>
            <div class="flex flex-wrap gap-1 mt-2">
                @foreach (\App\Imports\AsetImport::HEADINGS as $heading)
                <span class="badge badge-ghost badge-sm">{{ $heading }}</span>
                @endforeach
            </div>
            <ul class="text-xs text-base-content/70 list-disc pl-4 space-y-1 mt-2">
                <li>Angka boleh format Indonesia: <code>1.234.567,89</code>. Tanggal: <code>dd/mm/yyyy</code> atau tahun saja.</li>
                <li>Kategori dicocokkan dengan nama/kode kategori; bila belum ada akan dibuat otomatis.</li>
                <li>NUP kosong diisi berurutan per kode barang. Data dengan kode barang + NUP sama akan diperbarui.</li>
                <li>Kolom opsional: NUP, Jumlah (1), Luas, Harga Satuan, Akumulasi Penyusutan (0), Nilai Buku, Lokasi.</li>
            </ul>
        </x-ui.card>
    </div>
</x-layouts.app>
