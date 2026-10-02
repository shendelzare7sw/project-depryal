<x-layouts.app title="Import Data BMD" subtitle="Unggah Excel → periksa pratinjau → simpan">
    {{-- Alur 3 langkah --}}
    <ol class="grid min-w-0 grid-cols-3 gap-2 sm:gap-3">
        @foreach ([['Unggah berkas', 'Unggah', 'arrow-up-tray', true], ['Periksa pratinjau', 'Pratinjau', 'eye', false], ['Simpan data', 'Simpan', 'check-circle', false]] as $i => [$label, $pendek, $icon, $aktif])
        <li @class(['flex min-w-0 flex-col items-center gap-1.5 rounded-2xl border p-3 text-center sm:flex-row sm:gap-3 sm:p-4 sm:text-left', 'border-brand-200 bg-brand-50' => $aktif, 'border-zinc-200/80 bg-white' => ! $aktif])>
            <span @class(['flex h-9 w-9 shrink-0 items-center justify-center rounded-xl', 'bg-brand-700 text-white' => $aktif, 'bg-zinc-100 text-zinc-500' => ! $aktif])>
                <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" />
            </span>
            <span class="min-w-0">
                <span class="hidden text-[10px] font-bold uppercase tracking-wide text-zinc-400 sm:block">Langkah {{ $i + 1 }}</span>
                <span class="block text-xs font-bold text-zinc-800 sm:hidden">{{ $i + 1 }}. {{ $pendek }}</span>
                <span class="hidden truncate text-sm font-bold text-zinc-800 sm:block">{{ $label }}</span>
            </span>
        </li>
        @endforeach
    </ol>

    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_26rem]">
        <x-ui.card title="Unggah berkas Excel BMD" icon="arrow-up-tray">
            <x-slot:actions>
                <x-ui.btn tone="white" icon="arrow-left" :href="route('aset.index')" compact>Kembali</x-ui.btn>
            </x-slot:actions>
            <form method="POST" action="{{ route('aset.import.store') }}" enctype="multipart/form-data"
                x-data="{ busy: false, nama: '' }" @submit="busy = true" class="space-y-4">
                @csrf
                <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50/40 px-4 py-10 text-center transition hover:border-brand-400 hover:bg-brand-50">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-700 text-white shadow-lg shadow-brand-900/20"><x-heroicon-o-document-arrow-up class="h-7 w-7" /></span>
                    <span class="text-sm font-extrabold text-zinc-900" x-text="nama || 'Pilih berkas .xlsx / .xls'"></span>
                    <span class="text-xs text-zinc-500">Maksimal 12 MB · data belum disimpan pada langkah ini</span>
                    <input type="file" name="file" accept=".xlsx,.xls" required class="sr-only" @change="nama = $event.target.files[0]?.name ?? ''">
                </label>
                @error('file')<p class="flex items-center gap-1 text-xs font-medium text-rose-600"><x-heroicon-m-exclamation-circle class="h-4 w-4" /> {{ $message }}</p>@enderror
                <x-ui.btn type="submit" tone="primary" icon="eye" class="w-full" ::disabled="busy || !nama">
                    <span x-text="busy ? 'Membaca berkas…' : 'Unggah & Pratinjau'">Unggah &amp; Pratinjau</span>
                </x-ui.btn>
            </form>
        </x-ui.card>

        <aside class="min-w-0 space-y-5">
            <x-ui.card title="Template & format kolom" icon="table-cells" icon-tone="text-emerald-600">
                <x-ui.btn tone="soft-emerald" icon="document-arrow-down" :href="route('aset.import.template')" class="w-full">Unduh Template Excel</x-ui.btn>
                <div class="flex flex-wrap gap-1.5">
                    @foreach (\App\Imports\AsetImport::HEADINGS as $heading)
                    <x-ui.badge>{{ $heading }}</x-ui.badge>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Aturan pembacaan" icon="light-bulb" icon-tone="text-amber-500">
                <ul class="space-y-2.5 text-xs leading-5 text-zinc-600">
                    @foreach ([
                        'Angka format Indonesia (1.234.567,89) dan tanggal dd/mm/yyyy atau tahun saja terbaca otomatis.',
                        'Baris judul boleh berada di 10 baris teratas sheet pertama.',
                        'Kategori dicocokkan dengan nama/kode; bila belum ada akan dibuat otomatis.',
                        'NUP kosong diisi berurutan per kode barang. Kode barang + NUP yang sama akan diperbarui.',
                        'Opsional: NUP, Jumlah (1), Luas, Harga Satuan, Akumulasi Penyusutan (0), Nilai Buku, Lokasi.',
                    ] as $aturan)
                    <li class="flex gap-2"><x-heroicon-s-check-circle class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" />{{ $aturan }}</li>
                    @endforeach
                </ul>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
