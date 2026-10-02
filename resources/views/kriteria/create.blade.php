<x-layouts.app title="Tambah Kriteria" subtitle="Bobot dalam persen · rubrik skala 1–5">
    @include('kriteria._form', ['action' => route('kriteria.store'), 'method' => 'POST'])
</x-layouts.app>
