<x-layouts.app :title="$aset->nama_barang">
    <x-ui.page-header :title="$aset->nama_barang" subtitle="Detail Barang Milik Daerah">
        <x-slot:actions>
            <a href="{{ route('aset.index') }}" class="btn btn-outline btn-sm gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali
            </a>
            @if (auth()->user()->isOperator())
            <a href="{{ route('aset.edit', $aset) }}" class="btn btn-primary btn-sm gap-1">
                <x-heroicon-o-pencil-square class="w-4 h-4" /> Ubah
            </a>
            <x-confirm-form :action="route('aset.destroy', $aset)" method="DELETE" title="Hapus aset?"
                text="Aset akan dihapus dari daftar. Aset yang sudah tercatat pada periode final tidak dapat dihapus."
                confirm="Ya, hapus">
                <button type="submit" class="btn btn-error btn-outline btn-sm gap-1">
                    <x-heroicon-o-trash class="w-4 h-4" /> Hapus
                </button>
            </x-confirm-form>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($aset->perlu_perhatian)
    <div role="alert" class="alert alert-warning mb-4 text-sm">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
        <span>Sisa umur ekonomis tinggal <strong>{{ $aset->sisa_ueb }} tahun</strong> — aset ini perlu perhatian.</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <x-ui.card title="Data BMD">
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-3">
                    <x-ui.detail-item label="Kode Barang"><span class="font-mono">{{ $aset->kode_barang }}</span></x-ui.detail-item>
                    <x-ui.detail-item label="NUP">{{ $aset->nup }}</x-ui.detail-item>
                    <x-ui.detail-item label="Kategori">{{ $aset->kategori?->nama ?? '-' }}</x-ui.detail-item>
                    <x-ui.detail-item label="Jumlah">{{ $aset->jumlah }}</x-ui.detail-item>
                    <x-ui.detail-item label="Luas">{{ $aset->luas !== null ? \App\Support\Rupiah::format($aset->luas, false).' m²' : '-' }}</x-ui.detail-item>
                    <x-ui.detail-item label="Tanggal Perolehan">{{ $aset->tanggal_perolehan?->format('d/m/Y') }}</x-ui.detail-item>
                    <x-ui.detail-item label="Harga Satuan"><x-rupiah :value="$aset->harga_satuan" /></x-ui.detail-item>
                    <x-ui.detail-item label="Nilai Perolehan"><x-rupiah :value="$aset->nilai_perolehan" /></x-ui.detail-item>
                    <x-ui.detail-item label="Umur Ekonomis (UEB)">{{ $aset->umur_ekonomis }} tahun</x-ui.detail-item>
                    <x-ui.detail-item label="Akumulasi Penyusutan"><x-rupiah :value="$aset->akumulasi_penyusutan" /></x-ui.detail-item>
                    <x-ui.detail-item label="Sisa UEB">{{ $aset->sisa_ueb }} tahun</x-ui.detail-item>
                    <x-ui.detail-item label="Nilai Buku"><x-rupiah :value="$aset->nilai_buku" /></x-ui.detail-item>
                    <x-ui.detail-item label="Lokasi" class="col-span-2">{{ $aset->lokasi ?? '-' }}</x-ui.detail-item>
                    <x-ui.detail-item label="Status"><x-ui.badge-status :status="$aset->status" /></x-ui.detail-item>
                </dl>
            </x-ui.card>
        </div>

        <x-ui.card title="Foto Aset">
            @forelse ($aset->fotos as $foto)
            <figure class="relative">
                <a href="{{ $foto->url }}" target="_blank" rel="noopener">
                    <img src="{{ $foto->url }}" alt="Foto {{ $aset->nama_barang }}" class="w-full aspect-video object-cover rounded-lg" loading="lazy">
                </a>
                @if (auth()->user()->isOperator())
                <x-confirm-form :action="route('aset.foto.destroy', [$aset, $foto])" method="DELETE"
                    title="Hapus foto?" text="Foto akan dihapus permanen." confirm="Ya, hapus" class="absolute top-2 right-2">
                    <button type="submit" class="btn btn-circle btn-sm btn-error" aria-label="Hapus foto">
                        <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                </x-confirm-form>
                @endif
            </figure>
            @empty
            <x-ui.empty-state icon="camera" title="Belum ada foto" text="Tambahkan foto melalui tombol Ubah." />
            @endforelse
        </x-ui.card>
    </div>
</x-layouts.app>
