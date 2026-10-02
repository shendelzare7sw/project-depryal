{{-- Badge ⚠ "Perlu perhatian" bila sisa UEB ≤ 3 tahun --}}
@props(['aset'])
@if ($aset->perlu_perhatian)
<x-ui.badge tone="warning" title="Sisa umur ekonomis {{ $aset->sisa_ueb }} tahun">
    <x-heroicon-s-exclamation-triangle class="h-3 w-3" /> Perlu perhatian
</x-ui.badge>
@endif
