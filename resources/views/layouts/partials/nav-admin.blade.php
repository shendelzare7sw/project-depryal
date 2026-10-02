{{-- Navigasi: Administrator --}}
<li class="menu-title text-[10px] uppercase tracking-wider opacity-50 px-2 mt-2">Dashboard</li>
<li>
    <a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>
        <x-heroicon-o-home class="w-4 h-4" /> Beranda
    </a>
</li>

<li class="menu-title text-[10px] uppercase tracking-wider opacity-50 px-2 mt-3">Master Data</li>
<li>
    <a href="{{ route('pengguna.index') }}" @class(['active' => request()->routeIs('pengguna.*')])>
        <x-heroicon-o-users class="w-4 h-4" /> Pengguna
    </a>
</li>
<li>
    <a href="{{ route('aset.index') }}" @class(['active' => request()->routeIs('aset.*')])>
        <x-heroicon-o-building-office-2 class="w-4 h-4" /> Data Aset
    </a>
</li>
<li>
    <a href="{{ route('kriteria.index') }}" @class(['active' => request()->routeIs('kriteria.*')])>
        <x-heroicon-o-list-bullet class="w-4 h-4" /> Kriteria
    </a>
</li>

<li class="menu-title text-[10px] uppercase tracking-wider opacity-50 px-2 mt-3">Penilaian</li>
<li>
    <a href="{{ route('periode.index') }}" @class(['active' => request()->routeIs('periode.*')])>
        <x-heroicon-o-calendar-days class="w-4 h-4" /> Periode Penilaian
    </a>
</li>
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

<li class="menu-title text-[10px] uppercase tracking-wider opacity-50 px-2 mt-3">Sistem</li>
<li>
    <a href="{{ route('pengaturan.index') }}" @class(['active' => request()->routeIs('pengaturan.*')])>
        <x-heroicon-o-cog-6-tooth class="w-4 h-4" /> Pengaturan
    </a>
</li>
