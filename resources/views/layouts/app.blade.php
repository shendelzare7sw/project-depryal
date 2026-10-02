<!DOCTYPE html>
<html lang="id" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SIKASET' }} — SPK Kelayakan Aset</title>
    <meta name="description" content="Sistem Pendukung Keputusan Penilaian Kelayakan Aset Kecamatan Batuceper">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpeg') }}">
    @include('layouts.partials.assets')
</head>
<body class="bg-base-200 font-sans min-h-screen">

{{-- Mobile Drawer wrapper --}}
<div class="drawer lg:drawer-open">
    <input id="app-drawer" type="checkbox" class="drawer-toggle">

    {{-- PAGE CONTENT --}}
    <div class="drawer-content flex flex-col min-h-screen">

        {{-- Top Navbar --}}
        <div class="navbar bg-base-100 shadow-sm sticky top-0 z-30 px-3 md:px-6 border-b border-base-200">
            {{-- Hamburger (mobile only) --}}
            <div class="flex-none lg:hidden">
                <label for="app-drawer" aria-label="Buka menu" class="btn btn-square btn-ghost min-h-[44px]">
                    <x-heroicon-o-bars-3 class="w-6 h-6" />
                </label>
            </div>

            {{-- Brand (mobile) --}}
            <div class="flex-1 lg:hidden ml-2 flex items-center gap-2">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Logo" class="w-7 h-7 object-contain rounded">
                <span class="text-base font-extrabold tracking-tight text-primary">SIKASET</span>
            </div>

            {{-- Right side --}}
            <div class="flex-none ml-auto">
                <div class="dropdown dropdown-end" x-data="{ open: false }">
                    <button @click="open = !open" class="btn btn-ghost gap-2 min-h-[44px] h-11 px-3">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-8">
                                <span class="text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <span class="hidden md:inline text-sm font-medium max-w-[160px] truncate text-left">
                            {{ auth()->user()->name }}
                        </span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 hidden md:inline opacity-60" />
                    </button>
                    <ul x-show="open" @click.outside="open = false" x-cloak
                        class="dropdown-content menu bg-base-100 rounded-box shadow-xl border border-base-200 w-56 p-2 z-50 mt-2">
                        <li class="menu-title text-xs opacity-60 px-3 py-1">
                            {{ auth()->user()->role->label() }}
                        </li>
                        <li>
                            <a href="{{ route('profil.edit') }}" class="flex items-center gap-2 min-h-[44px]">
                                <x-heroicon-o-user class="w-4 h-4" />
                                Profil Saya
                            </a>
                        </li>
                        <div class="divider my-1"></div>
                        <li>
                            <x-confirm-form action="{{ route('logout') }}" method="POST"
                                title="Keluar dari sistem?" text="Anda akan mengakhiri sesi login saat ini."
                                confirm="Ya, Keluar" icon="question">
                                <button type="submit" class="flex items-center gap-2 w-full min-h-[44px] text-error hover:bg-error/10">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                    Keluar
                                </button>
                            </x-confirm-form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <main class="flex-1 p-4 md:p-6 max-w-7xl mx-auto w-full pb-20 lg:pb-6">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="footer footer-center text-base-content/40 text-xs py-3 px-4 hidden lg:flex border-t border-base-200 bg-base-100">
            <p>© {{ date('Y') }} SIKASET — Sistem Pendukung Keputusan Kelayakan Aset BMD Kecamatan Batuceper</p>
        </footer>

        {{-- Mobile Bottom Nav (Thumb-friendly on <lg) --}}
        <div class="btm-nav btm-nav-md border-t border-base-200 bg-base-100 z-20 lg:hidden shadow-lg">
            <a href="{{ route('dashboard') }}" @class(['active text-primary' => request()->routeIs('dashboard')])>
                <x-heroicon-o-home class="w-5 h-5" />
                <span class="btm-nav-label text-[10px]">Beranda</span>
            </a>
            @if (auth()->user()->role->value === 'pimpinan')
            <a href="{{ route('hasil.index') }}" @class(['active text-primary' => request()->routeIs('hasil.*', 'peringkat.*')])>
                <x-heroicon-o-chart-bar class="w-5 h-5" />
                <span class="btm-nav-label text-[10px]">Peringkat</span>
            </a>
            @else
            <a href="{{ route('aset.index') }}" @class(['active text-primary' => request()->routeIs('aset.*')])>
                <x-heroicon-o-building-office-2 class="w-5 h-5" />
                <span class="btm-nav-label text-[10px]">Aset</span>
            </a>
            <a href="{{ route('periode.index') }}" @class(['active text-primary' => request()->routeIs('periode.*')])>
                <x-heroicon-o-calendar-days class="w-5 h-5" />
                <span class="btm-nav-label text-[10px]">Periode</span>
            </a>
            @endif
            <label for="app-drawer" class="cursor-pointer">
                <x-heroicon-o-bars-3 class="w-5 h-5" />
                <span class="btm-nav-label text-[10px]">Menu</span>
            </label>
        </div>
    </div>

    {{-- SIDEBAR DRAWER --}}
    <div class="drawer-side z-40">
        <label for="app-drawer" class="drawer-overlay"></label>
        <aside class="bg-base-100 w-64 min-h-full flex flex-col shadow-xl border-r border-base-200">
            {{-- Logo --}}
            <div class="p-4 border-b border-base-200 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('img/logo.jpeg') }}" alt="Logo" class="w-9 h-9 object-contain rounded-lg">
                    <div>
                        <span class="text-lg font-black tracking-tight text-primary leading-none">SIKASET</span>
                        <p class="text-[11px] text-base-content/60 font-medium">Kec. Batuceper</p>
                    </div>
                </div>
                <label for="app-drawer" class="btn btn-ghost btn-xs btn-square lg:hidden">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </label>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-3">
                <ul class="menu menu-md px-3 gap-0.5">
                    @include('layouts.partials.nav-' . auth()->user()->role->value)
                </ul>
            </nav>

            {{-- User chip --}}
            <div class="p-4 border-t border-base-200">
                <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 transition">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-9">
                            <span class="text-sm font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-base-content/50">{{ auth()->user()->role->label() }}</p>
                    </div>
                </a>
            </div>
        </aside>
    </div>
</div>

<x-flash />

</body>
</html>
