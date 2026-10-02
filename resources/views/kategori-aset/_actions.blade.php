{{-- Aksi per kategori ($item); dipakai di tabel & kartu --}}
<a href="{{ route('kategori-aset.edit', $item) }}" class="btn btn-ghost btn-sm min-h-[44px] md:min-h-0">
    <x-heroicon-o-pencil-square class="w-4 h-4" /> Ubah
</a>
@if ($item->aset_count === 0)
<x-confirm-form :action="route('kategori-aset.destroy', $item)" method="DELETE" title="Hapus kategori?"
    text="Kategori {{ $item->nama }} akan dihapus." confirm="Ya, hapus">
    <button type="submit" class="btn btn-ghost btn-sm text-error min-h-[44px] md:min-h-0">
        <x-heroicon-o-trash class="w-4 h-4" /> Hapus
    </button>
</x-confirm-form>
@endif
