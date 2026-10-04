{{-- Isian kunci rahasia: nilai tersimpan tidak pernah ditampilkan; kosong = tidak berubah; centang untuk menghapus. --}}
@props(['name', 'label', 'tersimpan' => false, 'placeholder' => ''])
<div x-data="{ hapus: false }">
    <x-form.input :name="$name" :label="$label" type="password" autocomplete="off" x-bind:disabled="hapus"
        :placeholder="$tersimpan ? '•••••••• (tersimpan — kosongkan bila tidak diubah)' : $placeholder"
        :hint="$tersimpan ? 'Tersimpan terenkripsi' : 'Belum diatur'" {{ $attributes }} />
    @if ($tersimpan)
    <label class="mt-2 inline-flex min-h-10 cursor-pointer items-center gap-2 text-xs font-semibold text-rose-700">
        <input type="checkbox" name="hapus_{{ $name }}" value="1" x-model="hapus" class="checkbox checkbox-xs checkbox-error">
        Hapus {{ mb_strtolower($label) }} tersimpan
    </label>
    @endif
</div>
