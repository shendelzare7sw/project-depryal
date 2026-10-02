<x-layouts.app title="Ubah Kriteria" subtitle="{{ $kriteria->kode }} — {{ $kriteria->nama }}">
    @include('kriteria._form', ['action' => route('kriteria.update', $kriteria), 'method' => 'PUT'])
</x-layouts.app>
