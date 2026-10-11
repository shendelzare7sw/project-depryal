<x-layouts.app title="Peringkat Aset" subtitle="Peringkat kelayakan aset per periode">
    <x-ui.card>
        <x-ui.empty-state icon="chart-bar" title="Belum ada periode penilaian" text="Peringkat muncul setelah operator membuat periode, mengisi nilai, dan menjalankan perhitungan.">
            @if (auth()->user()->isOperator())<x-ui.btn tone="primary" icon="calendar-days" :href="route('periode.index')">Ke Periode Penilaian</x-ui.btn>@endif
        </x-ui.empty-state>
    </x-ui.card>
</x-layouts.app>
