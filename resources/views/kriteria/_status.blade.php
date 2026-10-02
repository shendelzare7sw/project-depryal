{{-- Badge status kriteria ($item); dipakai di tabel & kartu --}}
<span @class(['badge badge-sm', 'badge-success' => $item->is_active, 'badge-ghost' => ! $item->is_active])>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
@if ($item->dipakai_final)
<span class="badge badge-neutral badge-sm gap-1" title="Dipakai periode final: tipe & skala terkunci"><x-heroicon-s-lock-closed class="w-3 h-3" /> Terkunci</span>
@endif
