{{-- Tombol keluarkan aset dari periode (konfirmasi). Variabel: $periode, $item, $compact (ponsel = tombol btn, desktop = ikon tabel) --}}
<x-confirm-form :action="route('periode.aset.destroy', [$periode, $item])" method="DELETE" title="Keluarkan aset dari periode?"
    text="{{ $item->nama_barang }} tidak lagi dinilai pada periode ini dan nilainya dihapus. Data aset tetap ada." confirm="Ya, keluarkan">
    @if ($compact)
    <x-ui.btn type="submit" tone="soft-rose" icon="minus-circle" aria-label="Keluarkan dari periode" />
    @else
    <x-ui.table-action type="submit" tone="delete" icon="minus-circle" label="Keluarkan dari periode" />
    @endif
</x-confirm-form>
