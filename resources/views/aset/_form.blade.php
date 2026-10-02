{{-- Form aset 2 tahap: (1) Data BMD → (2) Kondisi & Foto. Variabel: $aset, $kategori, $action, $method --}}
@php
    $stepAwal = $errors->isNotEmpty() && collect($errors->keys())->every(fn ($k) => $k === 'status' || str_starts_with($k, 'fotos')) ? 2 : 1;
    $judul = $aset->exists ? 'Perbarui data aset' : 'Tambah aset baru';
    $kembali = $aset->exists ? route('aset.show', $aset) : route('aset.index');
@endphp
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ step: {{ $stepAwal }}, busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ $kembali }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali">
                    <x-heroicon-o-arrow-left class="h-5 w-5" />
                </a>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">{{ $judul }}</h2>
                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $aset->exists ? $aset->nama_barang : 'Isi data BMD, lalu kondisi & foto aset' }}</p>
                </div>
            </div>
            <ol class="grid grid-cols-2 gap-2 lg:w-96">
                @foreach ([1 => 'Data BMD', 2 => 'Kondisi & Foto'] as $n => $label)
                <li>
                    <button type="button" @click="{{ $n === 1 ? 'step = 1' : '$el.form.reportValidity() && (step = 2)' }}"
                        class="flex h-11 w-full items-center gap-2 rounded-xl px-3 text-left text-xs font-bold ring-1 ring-inset transition"
                        :class="step === {{ $n }} ? 'bg-brand-700 text-white ring-brand-700' : (step > {{ $n }} ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-white text-zinc-500 ring-zinc-200')">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-[11px]" :class="step === {{ $n }} ? 'bg-white/20' : 'bg-zinc-100'">{{ $n }}</span>
                        <span class="truncate">{{ $label }}</span>
                    </button>
                </li>
                @endforeach
            </ol>
        </header>

        @if ($errors->any())
        <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:mx-5">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
            <div>
                <p class="font-bold">Periksa kembali {{ $errors->count() }} isian berikut:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-4">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif
    </section>

    {{-- Tahap 1: Data BMD --}}
    <div x-show="step === 1" class="grid min-w-0 gap-5 xl:grid-cols-2">
        <x-ui.card title="Identitas barang" icon="identification">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2"><x-form.select name="kategori_aset_id" label="Kategori" :options="$kategori" :value="$aset->kategori_aset_id" required /></div>
                <div class="sm:col-span-2"><x-form.input name="nama_barang" label="Nama barang" :value="$aset->nama_barang" required maxlength="255" placeholder="Gedung Kantor Camat Batuceper" /></div>
                <x-form.input name="kode_barang" label="Kode barang" :value="$aset->kode_barang" required maxlength="50" placeholder="1.03.01.01.001" />
                <x-form.input name="nup" label="NUP" type="number" :value="$aset->nup" required min="1" hint="No. urut pendaftaran" />
                <x-form.input name="jumlah" label="Jumlah" type="number" :value="$aset->jumlah" required min="1" />
                <x-form.input name="luas" label="Luas (m²)" type="number" :value="$aset->luas" min="0" step="0.01" />
                <div class="sm:col-span-2"><x-form.input name="lokasi" label="Lokasi" :value="$aset->lokasi" maxlength="255" placeholder="Jl. Raya Batuceper No. 1" /></div>
            </div>
        </x-ui.card>

        <x-ui.card title="Nilai & umur ekonomis" icon="banknotes" icon-tone="text-emerald-600">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input name="tanggal_perolehan" label="Tanggal perolehan" type="date" :value="$aset->tanggal_perolehan?->format('Y-m-d')" required />
                <x-form.input name="harga_satuan" label="Harga satuan (Rp)" type="number" :value="$aset->harga_satuan" required min="0" step="0.01" />
                <x-form.input name="nilai_perolehan" label="Nilai perolehan (Rp)" type="number" :value="$aset->nilai_perolehan" required min="0" step="0.01" />
                <x-form.input name="akumulasi_penyusutan" label="Akumulasi penyusutan (Rp)" type="number" :value="$aset->akumulasi_penyusutan" required min="0" step="0.01" />
                <x-form.input name="nilai_buku" label="Nilai buku (Rp)" type="number" :value="$aset->nilai_buku" required min="0" step="0.01" />
                <x-form.input name="umur_ekonomis" label="Umur ekonomis / UEB (th)" type="number" :value="$aset->umur_ekonomis" required min="0" />
                <div class="sm:col-span-2"><x-form.input name="sisa_ueb" label="Sisa UEB (tahun)" type="number" :value="$aset->sisa_ueb" required min="0" hint="≤ 3 tahun = perlu perhatian" /></div>
            </div>
        </x-ui.card>
    </div>

    {{-- Tahap 2: Kondisi & Foto --}}
    <div x-show="step === 2" x-cloak class="grid min-w-0 gap-5 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <x-ui.card title="Status / kondisi" icon="clipboard-document-check">
            @php($statusTerpilih = old('status', $aset->status?->value))
            <div class="space-y-2">
                @foreach (\App\Enums\StatusAset::cases() as $status)
                <label class="flex min-h-[3.25rem] cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                    <input type="radio" name="status" value="{{ $status->value }}" class="radio radio-sm radio-primary" @checked($statusTerpilih === $status->value) required>
                    <span class="flex-1 text-sm font-semibold text-zinc-800">{{ $status->label() }}</span>
                    <x-ui.badge :tone="$status->color()" dot>{{ $status->label() }}</x-ui.badge>
                </label>
                @endforeach
            </div>
            @error('status')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
        </x-ui.card>

        <x-ui.card title="Foto aset" icon="camera" icon-tone="text-violet-500" subtitle="Maks. 5 foto, masing-masing ≤ 4 MB">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2" x-data="{ kamera: [], galeri: [] }">
                <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-brand-200 bg-brand-50/40 p-5 text-center transition hover:border-brand-400">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-white"><x-heroicon-o-camera class="h-5 w-5" /></span>
                    <span class="text-sm font-bold text-zinc-800">Ambil dari kamera</span>
                    <span class="text-xs text-zinc-500" x-text="kamera.length ? kamera.length + ' foto dipilih' : 'Langsung memotret kondisi aset'"></span>
                    <input type="file" name="fotos[]" accept="image/*" capture="environment" class="sr-only" @change="kamera = [...$event.target.files].map(f => URL.createObjectURL(f))">
                </label>
                <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-5 text-center transition hover:border-zinc-400">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-800 text-white"><x-heroicon-o-photo class="h-5 w-5" /></span>
                    <span class="text-sm font-bold text-zinc-800">Pilih dari galeri</span>
                    <span class="text-xs text-zinc-500" x-text="galeri.length ? galeri.length + ' foto dipilih' : 'Bisa memilih beberapa foto'"></span>
                    <input type="file" name="fotos[]" accept="image/*" multiple class="sr-only" @change="galeri = [...$event.target.files].map(f => URL.createObjectURL(f))">
                </label>
                <div class="grid grid-cols-3 gap-2 sm:col-span-2 md:grid-cols-5" x-show="kamera.length + galeri.length" x-cloak>
                    <template x-for="src in [...kamera, ...galeri]" :key="src">
                        <img :src="src" alt="Pratinjau foto" class="aspect-square w-full rounded-xl object-cover ring-1 ring-zinc-200">
                    </template>
                </div>
            </div>
            @error('fotos')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
            @error('fotos.*')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror

            @if ($aset->exists && $aset->fotos->isNotEmpty())
            <div class="border-t border-zinc-100 pt-3">
                <p class="mb-2 text-xs font-semibold text-zinc-500">Foto tersimpan ({{ $aset->fotos->count() }}) — hapus melalui halaman detail.</p>
                <div class="grid grid-cols-4 gap-2 md:grid-cols-6">
                    @foreach ($aset->fotos as $foto)
                    <img src="{{ $foto->url }}" alt="Foto aset" class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                    @endforeach
                </div>
            </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Footer aksi (sticky di ponsel, di atas bottom-nav) --}}
    <div class="sticky bottom-[4.25rem] z-20 -mx-3 border-t border-zinc-200/80 bg-white/95 px-3 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:static lg:mx-0 lg:rounded-2xl lg:border lg:px-5">
        <div class="flex items-center justify-between gap-2">
            <p class="hidden text-xs text-zinc-500 sm:block" x-text="step === 1 ? 'Langkah 1 dari 2 — Data BMD' : 'Langkah 2 dari 2 — Kondisi & Foto'"></p>
            <div class="flex flex-1 gap-2 sm:flex-none">
                <x-ui.btn tone="white" :href="$kembali" x-show="step === 1">Batal</x-ui.btn>
                <x-ui.btn tone="white" icon="arrow-left" x-show="step === 2" x-cloak @click="step = 1">Kembali</x-ui.btn>
                <x-ui.btn tone="primary" class="flex-1 sm:flex-none" x-show="step === 1" @click="$el.form.reportValidity() && (step = 2)">
                    Lanjut: Kondisi &amp; Foto
                </x-ui.btn>
                <x-ui.btn type="submit" tone="primary" icon="check" class="flex-1 sm:flex-none" x-show="step === 2" x-cloak ::disabled="busy">
                    Simpan Aset
                </x-ui.btn>
            </div>
        </div>
    </div>
</form>
