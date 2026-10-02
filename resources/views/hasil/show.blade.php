<x-layouts.app title="Detail Penilaian Aset">
    <x-ui.page-header title="Detail Penilaian Aset" subtitle="Hasil metode MOORA untuk aset terkait">
        <x-slot:actions>
            <a href="{{ route('hasil.index') }}" class="btn btn-outline btn-sm gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.empty-state icon="chart-bar" title="Detail Penilaian Aset" text="Implementasi alur keputusan lengkap tersedia pada Fase 4." />
    </x-ui.card>
</x-layouts.app>
