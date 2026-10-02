{{-- Badge status kriteria ($item); dipakai kartu ponsel & tabel desktop --}}
@if ($mobileTipe ?? true)<x-ui.badge :tone="$item->tipe->color()" class="lg:hidden">{{ $item->tipe->label() }}</x-ui.badge>@endif
<x-ui.badge :tone="$item->is_active ? 'success' : 'ghost'" dot>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
@if ($item->dipakai_final)
<x-ui.badge tone="neutral" title="Dipakai periode final: tipe & skala terkunci"><x-heroicon-s-lock-closed class="h-3 w-3" /> Terkunci</x-ui.badge>
@endif
