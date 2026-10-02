<x-layouts.app title="Ubah Aset">
    <x-ui.page-header title="Ubah Aset" :subtitle="$aset->nama_barang" />

    @include('aset._form', ['action' => route('aset.update', $aset), 'method' => 'PUT'])
</x-layouts.app>
