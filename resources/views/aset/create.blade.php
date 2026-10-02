<x-layouts.app title="Tambah Aset" subtitle="Data BMD → Kondisi & Foto">
    @include('aset._form', ['action' => route('aset.store'), 'method' => 'POST'])
</x-layouts.app>
