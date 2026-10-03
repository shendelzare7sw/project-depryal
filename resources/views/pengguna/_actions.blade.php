{{-- Aksi per pengguna ($u, $saya, $mobile): ubah, aktif/nonaktif, reset password. Akun sendiri hanya bisa diubah. --}}
@php($diriSendiri = $u->is($saya))
@if ($mobile)
<a href="{{ route('pengguna.edit', $u) }}" @class(['inline-flex h-9 items-center justify-center gap-1 rounded-lg bg-amber-50 text-xs font-bold text-amber-700', 'col-span-3' => $diriSendiri])><x-heroicon-o-pencil-square class="h-4 w-4" /> Ubah</a>
@else
<x-ui.table-action :href="route('pengguna.edit', $u)" tone="edit" icon="pencil-square" label="Ubah pengguna" />
@endif
@unless ($diriSendiri)
<x-confirm-form :action="route('pengguna.status', $u)" :title="$u->is_active ? 'Nonaktifkan akun?' : 'Aktifkan akun?'"
    text="{{ $u->is_active ? $u->name.' tidak akan bisa login sampai diaktifkan kembali.' : $u->name.' dapat login kembali.' }}"
    :confirm="$u->is_active ? 'Ya, nonaktifkan' : 'Ya, aktifkan'" :danger="$u->is_active">
    @if ($mobile)
    <button type="submit" @class(['inline-flex h-9 w-full items-center justify-center gap-1 rounded-lg text-xs font-bold', 'bg-rose-50 text-rose-700' => $u->is_active, 'bg-emerald-50 text-emerald-700' => ! $u->is_active])>
        <x-dynamic-component :component="'heroicon-o-'.($u->is_active ? 'no-symbol' : 'check-circle')" class="h-4 w-4" /> {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
    </button>
    @else
    <x-ui.table-action type="submit" :tone="$u->is_active ? 'delete' : 'success'" :icon="$u->is_active ? 'no-symbol' : 'check-circle'" :label="$u->is_active ? 'Nonaktifkan' : 'Aktifkan'" />
    @endif
</x-confirm-form>
<x-confirm-form :action="route('pengguna.reset-password', $u)" title="Reset password?" text="Password baru acak akan dibuat dan ditampilkan sekali untuk {{ $u->name }}." confirm="Ya, reset" icon="question">
    @if ($mobile)
    <button type="submit" class="inline-flex h-9 w-full items-center justify-center gap-1 rounded-lg bg-sky-50 text-xs font-bold text-sky-700"><x-heroicon-o-key class="h-4 w-4" /> Reset</button>
    @else
    <x-ui.table-action type="submit" tone="view" icon="key" label="Reset password" />
    @endif
</x-confirm-form>
@endunless
