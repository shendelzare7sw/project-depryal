<!DOCTYPE html>
<html lang="id" data-theme="sikaset">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SIKASET' }} — SIKASET</title>
    <meta name="description" content="Sistem Pendukung Keputusan Penilaian Kelayakan Aset Kecamatan Batuceper">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpeg') }}">
    @include('layouts.partials.assets')
</head>
<body class="min-h-screen bg-base-200 font-sans text-zinc-800 antialiased">
@php($user = auth()->user())

<div class="drawer lg:drawer-open">
    <input id="app-drawer" type="checkbox" class="drawer-toggle">

    <div class="drawer-content flex min-h-screen min-w-0 flex-col">
        {{-- Topbar: judul halaman · notifikasi · pengguna --}}
        <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-white/90 backdrop-blur">
            <div class="flex h-[4.5rem] min-w-0 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <label for="app-drawer" aria-label="Buka menu"
                    class="flex h-11 w-11 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50 lg:hidden">
                    <x-heroicon-o-bars-3 class="h-5 w-5" />
                </label>
                <span class="hidden h-10 w-1.5 shrink-0 rounded-full bg-gradient-to-b from-brand-500 to-amber-400 sm:block" aria-hidden="true"></span>
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-sm font-extrabold text-zinc-900 sm:text-base">{{ $title ?? 'SIKASET' }}</h1>
                    <p class="mt-0.5 hidden truncate text-xs text-zinc-500 sm:block">{{ $subtitle ?? 'Kecamatan Batuceper · '.now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="ml-auto flex items-center gap-2">
                    <x-ui.notification-bell :items="$notifikasi" :unread="$notifikasiBelumDibaca" />
                    <x-ui.user-menu />
                </div>
            </div>
        </header>

        {{-- Konten: memenuhi lebar shell (tanpa max-w) --}}
        <main class="w-full min-w-0 flex-1 overflow-x-clip px-3 pb-[calc(4.75rem+env(safe-area-inset-bottom))] pt-4 sm:px-6 sm:pt-5 lg:px-8 lg:pb-8">
            <div class="w-full min-w-0 space-y-5">
                @if ($panduan)<x-ui.panduan :panduan="$panduan" :kunci="$kunciPanduan" />@endif
                {{ $slot }}
            </div>
        </main>

        <footer class="hidden border-t border-zinc-200/80 bg-white px-8 py-4 text-xs text-zinc-400 lg:flex lg:items-center lg:justify-between">
            <span>© {{ date('Y') }} SIKASET · Pemerintah Kecamatan Batuceper, Kota Tangerang</span>
            <span>SPK Kelayakan Aset BMD · Metode MOORA</span>
        </footer>

        {{-- Bottom navigation (ponsel) --}}
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden" aria-label="Navigasi utama">
            <div @class(['grid', ['grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4'][count($mobileNavigation)]])>
                @foreach ($mobileNavigation as $item)
                @php($active = request()->routeIs(...explode('|', $item['active'])))
                <a href="{{ route($item['route']) }}" @class([
                    'relative flex min-h-[3.75rem] flex-col items-center justify-center gap-1 text-[11px] font-bold',
                    'text-brand-700' => $active, 'text-zinc-500' => ! $active,
                ])>
                    @if ($active)<span class="absolute inset-x-6 top-0 h-0.5 rounded-full bg-brand-600"></span>@endif
                    <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-5 w-5" />
                    <span class="max-w-full truncate px-1">{{ $item['label'] }}</span>
                </a>
                @endforeach
                <label for="app-drawer" class="flex min-h-[3.75rem] cursor-pointer flex-col items-center justify-center gap-1 text-[11px] font-bold text-zinc-500">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5" />
                    <span>Menu</span>
                </label>
            </div>
        </nav>
    </div>

    {{-- Sidebar --}}
    <div class="drawer-side z-40">
        <label for="app-drawer" aria-label="Tutup menu" class="drawer-overlay"></label>
        <aside class="flex min-h-full w-72 flex-col bg-brand-950 text-white/70">
            <div class="flex h-[4.5rem] items-center gap-3 border-b border-white/10 px-5">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Logo Kecamatan Batuceper" class="h-10 w-10 rounded-xl bg-white object-contain p-0.5">
                <div class="min-w-0 flex-1">
                    <p class="text-base font-extrabold leading-tight tracking-tight text-white">SIKASET</p>
                    <p class="truncate text-[10px] font-bold uppercase tracking-[0.14em] text-amber-300/90">SPK Kelayakan Aset</p>
                </div>
                <label for="app-drawer" aria-label="Tutup menu" class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-white/60 hover:bg-white/10 lg:hidden">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </label>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5 [scrollbar-width:none]" aria-label="Menu samping">
                @foreach ($navigation as $group)
                <div>
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-white/35">{{ $group['label'] }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($group['items'] as $item)
                        @php($active = request()->routeIs(...explode('|', $item['active'])))
                        <li>
                            <a href="{{ route($item['route']) }}" @class([
                                'group flex min-h-[44px] items-center gap-3 rounded-xl px-3 text-sm font-semibold transition-colors',
                                'bg-white/10 text-white' => $active,
                                'hover:bg-white/5 hover:text-white' => ! $active,
                            ])>
                                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" @class(['h-5 w-5 shrink-0', 'text-amber-300' => $active, 'text-white/45 group-hover:text-white/80' => ! $active]) />
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if ($active)<span class="ml-auto h-1.5 w-1.5 rounded-full bg-amber-300"></span>@endif
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </nav>

            <a href="{{ route('profil.edit') }}" class="m-3 flex items-center gap-3 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10 transition hover:bg-white/10">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-300 text-sm font-extrabold text-brand-950">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold text-white">{{ $user->name }}</span>
                    <span class="block truncate text-[11px] text-white/50">{{ $user->role->label() }}</span>
                </span>
                <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-white/40" />
            </a>
        </aside>
    </div>
</div>

<x-flash />
</body>
</html>
