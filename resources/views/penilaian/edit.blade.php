<x-layouts.app title="Input Nilai" :subtitle="$periode->nama">
    @php
        $terisi = count(array_filter($nilai, fn ($n) => $n !== null));
        $dihitung = $periode->status->value === 'dihitung';
    @endphp
    <x-confirm-form :action="route('penilaian.update', [$periode, $aset])" method="PUT" enctype="multipart/form-data" :when="$dihitung"
        title="Simpan perubahan nilai?" text="Hasil MOORA dan keputusan pimpinan pada periode ini akan dihapus. Hitung MOORA perlu dijalankan ulang."
        confirm="Ya, simpan" icon="warning" class="flex min-w-0 flex-col gap-5">
    <div class="contents" x-data="{ kamera: [], galeri: [], lanjut: 0 }">
        <input type="hidden" name="lanjut" :value="lanjut">

        {{-- Header aset + navigasi --}}
        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <a href="{{ route('periode.show', $periode) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali ke periode"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">Aset {{ $posisi }} dari {{ $total }}</p>
                        <h2 class="mt-0.5 text-base font-extrabold text-zinc-900 sm:text-lg">{{ $aset->nama_barang }}</h2>
                        <p class="mt-1 truncate font-mono text-[11px] text-zinc-500">{{ $aset->kode_barang }} · NUP {{ $aset->nup }} · {{ $aset->kategori?->nama }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <x-ui.badge :tone="$terisi >= $kriteria->count() ? 'success' : 'ghost'" dot>{{ $terisi }}/{{ $kriteria->count() }} kriteria</x-ui.badge>
                            <x-ui.badge tone="info">Sisa UEB {{ $aset->sisa_ueb }} th</x-ui.badge>
                            <x-ui.badge-perhatian :aset="$aset" />
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <x-ui.btn tone="white" icon="chevron-left" :href="$sebelumnya ? route('penilaian.edit', [$periode, $sebelumnya]) : null" :disabled="! $sebelumnya">Sebelumnya</x-ui.btn>
                    <x-ui.btn tone="white" icon="chevron-right" :href="$berikutnya ? route('penilaian.edit', [$periode, $berikutnya]) : null" :disabled="! $berikutnya">Berikutnya</x-ui.btn>
                </div>
            </div>
            @if ($dihitung)
            <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 p-3 text-xs leading-5 text-sky-900 sm:mx-5">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-sky-600" />
                <p>Periode sudah dihitung. Menyimpan perubahan akan <strong>menghapus hasil MOORA & keputusan</strong>; Hitung MOORA perlu dijalankan ulang.</p>
            </div>
            @endif
            @if ($errors->any())
            <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:mx-5">
                <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
                <ul class="list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif
        </section>

        <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
            {{-- Nilai per kriteria --}}
            <div class="min-w-0 space-y-4">
                @forelse ($kriteria as $k)
                <x-form.scale-radio :kriteria="$k" :value="$nilai[$k->id] ?? null" />
                @empty
                <x-ui.card><x-ui.empty-state icon="scale" title="Belum ada kriteria aktif" text="Atur kriteria & bobot terlebih dahulu." /></x-ui.card>
                @endforelse
            </div>

            {{-- Kondisi & foto --}}
            <aside class="min-w-0 space-y-5">
                <x-ui.card title="Kondisi aset" icon="document-text" icon-tone="text-amber-500">
                    <x-form.textarea name="deskripsi_kondisi" label="Deskripsi kerusakan / kondisi" :value="$kondisi" rows="4" maxlength="2000"
                        placeholder="Mis. atap bocor di ruang tengah, cat dinding mengelupas, listrik normal." />
                </x-ui.card>

                <x-ui.card title="Foto kondisi" icon="camera" icon-tone="text-violet-500" subtitle="Maks. 5 foto per simpan, @ ≤ 4 MB">
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-brand-200 bg-brand-50/40 p-3 text-center hover:border-brand-400">
                            <x-heroicon-o-camera class="h-6 w-6 text-brand-600" />
                            <span class="text-xs font-bold text-zinc-800">Kamera</span>
                            <span class="text-[10px] text-zinc-500" x-text="kamera.length ? kamera.length + ' dipilih' : 'Potret langsung'"></span>
                            <input type="file" name="fotos[]" accept="image/*" capture="environment" class="sr-only" @change="kamera = [...$event.target.files].map(f => URL.createObjectURL(f))">
                        </label>
                        <label class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50 p-3 text-center hover:border-zinc-400">
                            <x-heroicon-o-photo class="h-6 w-6 text-zinc-600" />
                            <span class="text-xs font-bold text-zinc-800">Galeri</span>
                            <span class="text-[10px] text-zinc-500" x-text="galeri.length ? galeri.length + ' dipilih' : 'Pilih beberapa'"></span>
                            <input type="file" name="fotos[]" accept="image/*" multiple class="sr-only" @change="galeri = [...$event.target.files].map(f => URL.createObjectURL(f))">
                        </label>
                    </div>
                    <div class="grid grid-cols-4 gap-2" x-show="kamera.length + galeri.length" x-cloak>
                        <template x-for="src in [...kamera, ...galeri]" :key="src"><img :src="src" alt="Pratinjau" class="aspect-square w-full rounded-lg object-cover ring-1 ring-zinc-200"></template>
                    </div>
                    @error('fotos.*')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    @if ($fotos->isNotEmpty())
                    <div class="border-t border-zinc-100 pt-3">
                        <p class="mb-2 text-xs font-semibold text-zinc-500">Foto periode ini ({{ $fotos->count() }})</p>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($fotos as $foto)
                            <a href="{{ $foto->url }}" target="_blank" rel="noopener"><img src="{{ $foto->url }}" alt="Foto kondisi" class="aspect-square w-full rounded-lg object-cover" loading="lazy"></a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </x-ui.card>
            </aside>
        </div>

        {{-- Aksi sticky --}}
        <x-ui.action-bar sticky-desktop>
            <div class="flex items-center justify-between gap-2">
                <p class="hidden text-xs text-zinc-500 md:block">Nilai kosong boleh disimpan; aset dianggap lengkap bila semua kriteria terisi.</p>
                <div class="grid flex-1 grid-cols-2 gap-2 md:flex md:flex-none">
                    <x-ui.btn type="submit" tone="white" icon="check" @click="lanjut = 0" ::disabled="busy">Simpan</x-ui.btn>
                    <x-ui.btn type="submit" tone="primary" icon="arrow-right" @click="lanjut = 1" ::disabled="busy">Simpan &amp; Lanjut</x-ui.btn>
                </div>
            </div>
        </x-ui.action-bar>
    </div>
    </x-confirm-form>
</x-layouts.app>
