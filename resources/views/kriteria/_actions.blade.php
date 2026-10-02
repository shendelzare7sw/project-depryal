{{-- Aksi operator per kriteria ($item, $mobile). Hapus disembunyikan bila kriteria sudah punya nilai penilaian. --}}
@if ($mobile)
<a href="{{ route('kriteria.edit', $item) }}" @class(['inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-amber-50 text-xs font-bold text-amber-700', 'col-span-2' => $item->punya_nilai])><x-heroicon-o-pencil-square class="h-4 w-4" /> Ubah</a>
@else
<x-ui.table-action :href="route('kriteria.edit', $item)" tone="edit" icon="pencil-square" label="Ubah kriteria" />
@endif
@unless ($item->punya_nilai)
<x-confirm-form :action="route('kriteria.destroy', $item)" method="DELETE" title="Hapus kriteria?"
    text="Kriteria {{ $item->kode }} — {{ $item->nama }} beserta rubriknya akan dihapus." confirm="Ya, hapus">
    @if ($mobile)
    <button type="submit" class="inline-flex h-9 w-full items-center justify-center gap-1 rounded-lg bg-rose-50 text-xs font-bold text-rose-700"><x-heroicon-o-trash class="h-4 w-4" /> Hapus</button>
    @else
    <x-ui.table-action type="submit" tone="delete" icon="trash" label="Hapus kriteria" />
    @endif
</x-confirm-form>
@endunless
