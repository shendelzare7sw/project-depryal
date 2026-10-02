{{-- Aksi per kategori ($item); dipakai kartu ponsel & tabel desktop --}}
<x-ui.table-action :href="route('kategori-aset.edit', $item)" tone="edit" icon="pencil-square" label="Ubah kategori" />
@if ($item->aset_count === 0)
<x-confirm-form :action="route('kategori-aset.destroy', $item)" method="DELETE" title="Hapus kategori?"
    text="Kategori {{ $item->nama }} akan dihapus." confirm="Ya, hapus">
    <x-ui.table-action type="submit" tone="delete" icon="trash" label="Hapus kategori" />
</x-confirm-form>
@endif
