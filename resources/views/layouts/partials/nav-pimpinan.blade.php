{{-- Navigasi: Pimpinan --}}
<li class="menu-title text-[10px] uppercase tracking-wider opacity-50 px-2 mt-2">Dashboard</li>
<li>
    <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
        <x-heroicon-o-home class="w-4 h-4" /> Beranda
    </a>
</li>

<li class="menu-title text-[10px] uppercase tracking-wider opacity-50 px-2 mt-3">Keputusan</li>
<li>
    <a href="{{ route('hasil.index') }}" @class(['active' => request()->routeIs('hasil.*')])>
        <x-heroicon-o-chart-bar class="w-4 h-4" /> Hasil MOORA
    </a>
</li>
<li>
    <a href="{{ route('laporan.index') }}" @class(['active' => request()->routeIs('laporan.*')])>
        <x-heroicon-o-document-chart-bar class="w-4 h-4" /> Laporan
    </a>
</li>
