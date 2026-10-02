<x-layouts.app title="Tambah Kategori Aset" subtitle="Master data kategori">
    @include('kategori-aset._form', ['action' => route('kategori-aset.store'), 'method' => 'POST'])
</x-layouts.app>
