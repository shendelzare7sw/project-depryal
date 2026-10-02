{{-- Aksi operator per kriteria ($item); hapus disembunyikan bila kriteria sudah punya nilai penilaian --}}
<a href="{{ route('kriteria.edit', $item) }}" class="btn btn-ghost btn-sm min-h-[44px] md:min-h-0">
    <x-heroicon-o-pencil-square class="w-4 h-4" /> Ubah
</a>
@unless ($item->punya_nilai)
<x-confirm-form :action="route('kriteria.destroy', $item)" method="DELETE" title="Hapus kriteria?"
    text="Kriteria {{ $item->kode }} — {{ $item->nama }} beserta rubriknya akan dihapus." confirm="Ya, hapus">
    <button type="submit" class="btn btn-ghost btn-sm text-error min-h-[44px] md:min-h-0">
        <x-heroicon-o-trash class="w-4 h-4" /> Hapus
    </button>
</x-confirm-form>
@endunless
