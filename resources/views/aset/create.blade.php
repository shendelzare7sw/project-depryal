<x-layouts.app title="Tambah Aset">
    <x-ui.page-header title="Tambah Aset" subtitle="Isi data BMD, lalu kondisi & foto aset" />

    @include('aset._form', ['action' => route('aset.store'), 'method' => 'POST'])
</x-layouts.app>
