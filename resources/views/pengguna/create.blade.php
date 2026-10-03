<x-layouts.app title="Tambah Pengguna" subtitle="Buat akun baru untuk admin, operator, atau pimpinan">
    @include('pengguna._form', ['action' => route('pengguna.store'), 'method' => 'POST'])
</x-layouts.app>
