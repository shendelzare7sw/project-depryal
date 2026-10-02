<x-layouts.app title="Ubah Kategori Aset" :subtitle="$kategori->nama">
    @include('kategori-aset._form', ['action' => route('kategori-aset.update', $kategori), 'method' => 'PUT'])
</x-layouts.app>
