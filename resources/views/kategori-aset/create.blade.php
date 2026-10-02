<x-layouts.app title="Tambah Kategori Aset">
    <x-ui.page-header title="Tambah Kategori Aset" />

    @include('kategori-aset._form', ['action' => route('kategori-aset.store'), 'method' => 'POST'])
</x-layouts.app>
