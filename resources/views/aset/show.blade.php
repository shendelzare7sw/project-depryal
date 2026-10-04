<x-layouts.app :title="$aset->nama_barang" subtitle="Detail Barang Milik Daerah">
    @php($operator = auth()->user()->isOperator())

    {{-- Ringkasan aset --}}
    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <a href="{{ route('aset.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali ke daftar aset">
                    <x-heroicon-o-arrow-left class="h-5 w-5" />
                </a>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-extrabold text-zinc-900 sm:text-lg">{{ $aset->nama_barang }}</h2>
                        <x-ui.badge-status :status="$aset->status" />
                        <x-ui.badge-perhatian :aset="$aset" />
                    </div>
                    <p class="mt-1 font-mono text-xs text-zinc-500">{{ $aset->kode_barang }} · NUP {{ $aset->nup }} · {{ $aset->kategori?->nama ?? '-' }}</p>
                </div>
            </div>
            @if ($operator)
            <div class="grid grid-cols-2 gap-2 sm:flex">
                <x-ui.btn tone="soft-amber" icon="pencil-square" :href="route('aset.edit', $aset)">Ubah</x-ui.btn>
                <x-confirm-form :action="route('aset.destroy', $aset)" method="DELETE" title="Hapus aset?"
                    text="Aset akan dihapus dari daftar. Aset yang sudah tercatat pada periode final tidak dapat dihapus." confirm="Ya, hapus">
                    <x-ui.btn type="submit" tone="soft-rose" icon="trash" class="w-full">Hapus</x-ui.btn>
                </x-confirm-form>
            </div>
            @endif
        </div>
        <div class="grid grid-cols-2 border-t border-zinc-100 bg-zinc-50/60 lg:grid-cols-4">
            @foreach ([
                ['Nilai perolehan', \App\Support\Rupiah::format($aset->nilai_perolehan), 'banknotes', 'text-brand-700'],
                ['Nilai buku', \App\Support\Rupiah::format($aset->nilai_buku), 'wallet', 'text-emerald-600'],
                ['Sisa UEB', $aset->sisa_ueb.' / '.$aset->umur_ekonomis.' th', 'clock', $aset->perlu_perhatian ? 'text-amber-600' : 'text-sky-600'],
                ['Luas', $aset->luas !== null ? \App\Support\Rupiah::format($aset->luas, false).' m²' : '—', 'square-3-stack-3d', 'text-violet-600'],
            ] as [$label, $value, $icon, $tone])
            <div class="flex min-w-0 items-center gap-3 border-zinc-100 p-4 odd:border-r lg:border-r lg:last:border-r-0">
                <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5 shrink-0 {{ $tone }}" />
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-zinc-500">{{ $label }}</p>
                    <p class="truncate text-[13px] font-extrabold tabular-nums text-zinc-900 sm:text-sm" title="{{ $value }}">{{ $value }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    @if ($aset->perlu_perhatian)
    <div role="alert" class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-600" />
        <p>Sisa umur ekonomis tinggal <strong>{{ $aset->sisa_ueb }} tahun</strong>. Prioritaskan aset ini saat penilaian kelayakan.</p>
    </div>
    @endif

    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <x-ui.card title="Data BMD" icon="document-text">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-4 md:grid-cols-3">
                <x-ui.detail-item label="Kode barang"><span class="font-mono">{{ $aset->kode_barang }}</span></x-ui.detail-item>
                <x-ui.detail-item label="NUP">{{ $aset->nup }}</x-ui.detail-item>
                <x-ui.detail-item label="Kategori">{{ $aset->kategori?->nama ?? '-' }}</x-ui.detail-item>
                <x-ui.detail-item label="Jumlah">{{ $aset->jumlah }}</x-ui.detail-item>
                <x-ui.detail-item label="Tanggal perolehan">{{ $aset->tanggal_perolehan?->translatedFormat('d F Y') }}</x-ui.detail-item>
                <x-ui.detail-item label="Harga satuan"><x-rupiah :value="$aset->harga_satuan" /></x-ui.detail-item>
                <x-ui.detail-item label="Nilai perolehan"><x-rupiah :value="$aset->nilai_perolehan" /></x-ui.detail-item>
                <x-ui.detail-item label="Akumulasi penyusutan"><x-rupiah :value="$aset->akumulasi_penyusutan" /></x-ui.detail-item>
                <x-ui.detail-item label="Nilai buku"><x-rupiah :value="$aset->nilai_buku" /></x-ui.detail-item>
                <x-ui.detail-item label="Umur ekonomis (UEB)">{{ $aset->umur_ekonomis }} tahun</x-ui.detail-item>
                <x-ui.detail-item label="Sisa UEB">{{ $aset->sisa_ueb }} tahun</x-ui.detail-item>
                <x-ui.detail-item label="Lokasi" class="col-span-2 md:col-span-1">{{ $aset->lokasi ?? '-' }}</x-ui.detail-item>
            </dl>
        </x-ui.card>

        <x-ui.card title="Foto aset" icon="photo" icon-tone="text-violet-500">
            <x-slot:chip><x-ui.badge>{{ $aset->fotos->count() }}</x-ui.badge></x-slot:chip>
            @if ($aset->fotos->isNotEmpty())
            <div class="grid grid-cols-2 gap-2">
                @foreach ($aset->fotos as $foto)
                <figure class="group relative overflow-hidden rounded-xl bg-zinc-100">
                    <a href="{{ $foto->url }}" target="_blank" rel="noopener">
                        <img src="{{ $foto->url }}" alt="Foto {{ $aset->nama_barang }}" class="aspect-square w-full object-cover transition group-hover:scale-105" loading="lazy">
                    </a>
                    @if ($foto->periode)
                    <figcaption class="absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-black/70 to-transparent px-2 pb-1.5 pt-4 text-[10px] font-semibold text-white">
                        @if ($foto->periode->isFinal())<x-heroicon-s-lock-closed class="mr-0.5 inline h-3 w-3" />@endif{{ $foto->periode->nama }}
                    </figcaption>
                    @endif
                    @if ($operator && ! $foto->periode?->isFinal())
                    <x-confirm-form :action="route('aset.foto.destroy', [$aset, $foto])" method="DELETE" title="Hapus foto?" text="Foto akan dihapus permanen." confirm="Ya, hapus" class="absolute right-2 top-2">
                        <x-ui.table-action type="submit" tone="delete" icon="trash" label="Hapus foto" class="bg-white" />
                    </x-confirm-form>
                    @endif
                </figure>
                @endforeach
            </div>
            @else
            <x-ui.empty-state icon="camera" title="Belum ada foto" :text="$operator ? 'Tambahkan foto melalui tombol Ubah (kamera atau galeri).' : 'Operator belum menambahkan foto.'" />
            @endif
        </x-ui.card>
    </div>

    <x-ui.card title="Riwayat penilaian" icon="clock" icon-tone="text-sky-600" subtitle="Hasil MOORA & keputusan pimpinan pada setiap periode" flush>
        @forelse ($riwayat as $r)
        <div class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-3 gap-y-2 border-t border-zinc-100 p-4 first:border-t-0 sm:px-5 lg:grid-cols-[auto_minmax(0,1fr)_16rem_22rem]">
            <x-ui.rank :ranking="$r['hasil']->ranking" />
            <div class="min-w-0">
                <a href="{{ route('peringkat.show', [$r['periode'], $aset]) }}" class="block truncate text-sm font-extrabold text-zinc-900 hover:text-brand-700">{{ $r['periode']->nama }}</a>
                <p class="text-[11px] text-zinc-500">Peringkat {{ $r['hasil']->ranking }} dari {{ $r['total'] }} aset · {{ $r['periode']->status->label() }}</p>
            </div>
            <x-ui.skor :value="$r['hasil']->skor_relatif" :tindakan="$r['hasil']->rekomendasi" class="col-span-2 lg:col-span-1" />
            <div class="col-span-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 lg:col-span-1 lg:justify-end">
                <span class="inline-flex items-center gap-1.5"><span class="text-[11px] text-zinc-500">Rekomendasi</span> <x-ui.badge-tindakan :tindakan="$r['hasil']->rekomendasi" /></span>
                <span class="inline-flex items-center gap-1.5"><span class="text-[11px] text-zinc-500">Keputusan</span>
                @if ($r['keputusan'])<x-ui.badge-tindakan :tindakan="$r['keputusan']->tindakan" />@else<x-ui.badge tone="ghost">Belum</x-ui.badge>@endif</span>
            </div>
        </div>
        @empty
        <x-ui.empty-state icon="clock" title="Belum ada riwayat" text="Aset ini belum pernah masuk periode yang sudah dihitung MOORA." />
        @endforelse
    </x-ui.card>
</x-layouts.app>
