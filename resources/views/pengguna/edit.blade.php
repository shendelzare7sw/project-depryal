<x-layouts.app title="Ubah Pengguna" :subtitle="$user->name">
    @include('pengguna._form', ['action' => route('pengguna.update', $user), 'method' => 'PUT'])
</x-layouts.app>
