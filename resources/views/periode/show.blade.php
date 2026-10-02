<x-layouts.app :title="$periode->nama">
    <x-ui.page-header :title="$periode->nama" subtitle="Detail Periode Penilaian">
        <x-slot:actions>
            <a href="{{ route('periode.index') }}" class="btn btn-outline btn-sm gap-1">
                <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.empty-state icon="calendar-days" title="Detail Periode" text="Implementasi alur penilaian lengkap tersedia pada Fase 3." />
    </x-ui.card>
</x-layouts.app>
