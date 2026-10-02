<x-layouts.app title="Detail Perhitungan MOORA">
    <x-ui.page-header title="Detail Perhitungan MOORA" subtitle="Matriks Keputusan, Normalisasi, dan Optimasi Multi-Objektif">
        <x-slot:actions>
            <a href="{{ route('hasil.index') }}" class="btn btn-outline btn-sm gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.empty-state icon="chart-bar" title="Detail Perhitungan MOORA" text="Tabel visualisasi langkah matriks MOORA lengkap tersedia pada Fase 4." />
    </x-ui.card>
</x-layouts.app>
