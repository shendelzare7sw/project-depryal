{{-- Badge ⚠ "Perlu perhatian" bila sisa UEB ≤ 3 tahun --}}
@props(['aset'])
@if ($aset->perlu_perhatian)
<span class="badge badge-warning badge-sm gap-1 font-medium" title="Sisa umur ekonomis {{ $aset->sisa_ueb }} tahun">
    <x-heroicon-s-exclamation-triangle class="w-3 h-3" /> Perlu perhatian
</span>
@endif
