{{-- Form aset 2 tahap: (1) Data BMD → (2) Kondisi & Foto. Variabel: $aset, $kategori, $action, $method --}}
@php($stepAwal = $errors->isNotEmpty() && collect($errors->keys())->every(fn ($k) => $k === 'status' || str_starts_with($k, 'fotos')) ? 2 : 1)
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" x-data="{ step: {{ $stepAwal }}, busy: false }" @submit="busy = true">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <ul class="steps w-full mb-4 text-xs">
        <li class="step step-primary">Data BMD</li>
        <li class="step" :class="step === 2 && 'step-primary'">Kondisi &amp; Foto</li>
    </ul>

    <x-ui.card>
        <div x-show="step === 1" class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
            <x-form.select name="kategori_aset_id" label="Kategori" :options="$kategori" :value="$aset->kategori_aset_id" required />
            <x-form.input name="nama_barang" label="Nama Barang" :value="$aset->nama_barang" required maxlength="255" />
            <x-form.input name="kode_barang" label="Kode Barang" :value="$aset->kode_barang" required maxlength="50" placeholder="1.03.01.01.001" />
            <x-form.input name="nup" label="NUP" type="number" :value="$aset->nup" required min="1" hint="Nomor urut pendaftaran" />
            <x-form.input name="jumlah" label="Jumlah" type="number" :value="$aset->jumlah" required min="1" />
            <x-form.input name="luas" label="Luas (m²)" type="number" :value="$aset->luas" min="0" step="0.01" />
            <x-form.input name="tanggal_perolehan" label="Tanggal Perolehan" type="date" :value="$aset->tanggal_perolehan?->format('Y-m-d')" required />
            <x-form.input name="harga_satuan" label="Harga Satuan (Rp)" type="number" :value="$aset->harga_satuan" required min="0" step="0.01" />
            <x-form.input name="nilai_perolehan" label="Nilai Perolehan (Rp)" type="number" :value="$aset->nilai_perolehan" required min="0" step="0.01" />
            <x-form.input name="umur_ekonomis" label="Umur Ekonomis / UEB (tahun)" type="number" :value="$aset->umur_ekonomis" required min="0" />
            <x-form.input name="akumulasi_penyusutan" label="Akumulasi Penyusutan (Rp)" type="number" :value="$aset->akumulasi_penyusutan" required min="0" step="0.01" />
            <x-form.input name="sisa_ueb" label="Sisa UEB (tahun)" type="number" :value="$aset->sisa_ueb" required min="0" hint="≤ 3 tahun = perlu perhatian" />
            <x-form.input name="nilai_buku" label="Nilai Buku (Rp)" type="number" :value="$aset->nilai_buku" required min="0" step="0.01" />
            <x-form.input name="lokasi" label="Lokasi" :value="$aset->lokasi" maxlength="255" />
        </div>

        <div x-show="step === 2" x-cloak class="space-y-4 [&[x-cloak]]:hidden">
            <x-form.select name="status" label="Status / Kondisi Aset" :options="\App\Enums\StatusAset::options()" :value="$aset->status?->value" required />

            <x-form.field name="fotos" label="Foto Aset" hint="Maks. 5 foto, @ 4 MB">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label class="flex flex-col gap-1 text-xs">
                        <span class="flex items-center gap-1"><x-heroicon-o-camera class="w-4 h-4" /> Ambil dari kamera</span>
                        <input type="file" name="fotos[]" accept="image/*" capture="environment" class="file-input file-input-bordered w-full">
                    </label>
                    <label class="flex flex-col gap-1 text-xs">
                        <span class="flex items-center gap-1"><x-heroicon-o-photo class="w-4 h-4" /> Pilih dari galeri</span>
                        <input type="file" name="fotos[]" accept="image/*" multiple class="file-input file-input-bordered w-full">
                    </label>
                </div>
                @error('fotos.*')
                <span class="label-text-alt text-error mt-1">{{ $message }}</span>
                @enderror
            </x-form.field>

            @if ($aset->exists && $aset->fotos->isNotEmpty())
            <div>
                <p class="text-xs text-base-content/60 mb-1">Foto tersimpan ({{ $aset->fotos->count() }}) — hapus foto melalui halaman detail.</p>
                <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                    @foreach ($aset->fotos as $foto)
                    <img src="{{ $foto->url }}" alt="Foto aset" class="w-full aspect-square object-cover rounded" loading="lazy">
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </x-ui.card>

    {{-- Bar aksi: sticky di mobile agar terjangkau jempol --}}
    <div class="sticky bottom-16 lg:bottom-0 z-10 bg-base-200/95 py-3 mt-2 flex gap-2 justify-end">
        <a href="{{ $aset->exists ? route('aset.show', $aset) : route('aset.index') }}" class="btn btn-ghost min-h-[44px]" x-show="step === 1">Batal</a>
        <button type="button" class="btn btn-primary min-h-[44px] flex-1 md:flex-none" x-show="step === 1"
            @click="$el.form.reportValidity() && (step = 2)">
            Lanjut: Kondisi &amp; Foto <x-heroicon-o-arrow-right class="w-4 h-4" />
        </button>
        <button type="button" class="btn btn-ghost min-h-[44px] [&[x-cloak]]:hidden" x-show="step === 2" x-cloak @click="step = 1">
            <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali
        </button>
        <button type="submit" class="btn btn-primary min-h-[44px] flex-1 md:flex-none [&[x-cloak]]:hidden" x-show="step === 2" x-cloak :disabled="busy">
            <span class="loading loading-spinner loading-sm" x-show="busy"></span> Simpan Aset
        </button>
    </div>
</form>
