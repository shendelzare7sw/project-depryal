{{-- Lonceng notifikasi topbar. Panel: fixed selebar layar di ponsel, dropdown 24rem di ≥sm. --}}
@props(['items', 'unread' => 0])
@php
    $tones = [
        'success' => 'bg-emerald-50 text-emerald-600', 'warning' => 'bg-amber-50 text-amber-600',
        'error' => 'bg-rose-50 text-rose-600', 'info' => 'bg-sky-50 text-sky-600', 'primary' => 'bg-brand-50 text-brand-600',
    ];
@endphp
<div class="sm:relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Buka notifikasi"
        class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:border-brand-500 hover:text-brand-700">
        <x-heroicon-o-bell class="h-5 w-5" />
        @if ($unread > 0)
        <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <section x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
        class="fixed inset-x-4 top-[4.5rem] z-50 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl shadow-zinc-900/10 sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96"
        aria-label="Notifikasi terbaru">
        <header class="flex items-center justify-between gap-3 border-b border-zinc-100 bg-zinc-50 px-4 py-3">
            <div>
                <h2 class="text-sm font-extrabold text-zinc-900">Notifikasi</h2>
                <p class="text-[11px] text-zinc-500">{{ $unread > 0 ? "{$unread} belum dibaca" : 'Semua sudah dibaca' }}</p>
            </div>
            @if ($unread > 0)
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf
                <button type="submit" class="text-xs font-bold text-brand-700 hover:text-brand-900">Tandai dibaca</button>
            </form>
            @endif
        </header>
        <div class="max-h-[min(26rem,60vh)] divide-y divide-zinc-100 overflow-y-auto">
            @forelse ($items as $n)
            <a href="{{ route('notifikasi.baca', $n->id) }}" @class(['flex gap-3 px-4 py-3 transition hover:bg-zinc-50', 'bg-brand-50/40' => $n->read_at === null])>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tones[$n->data['tone'] ?? 'primary'] ?? $tones['primary'] }}">
                    <x-dynamic-component :component="'heroicon-o-'.($n->data['icon'] ?? 'bell')" class="h-4 w-4" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span class="truncate text-sm font-bold text-zinc-900">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
                        @if ($n->read_at === null)<span class="h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>@endif
                    </span>
                    <span class="mt-0.5 line-clamp-2 block text-xs leading-5 text-zinc-500">{{ $n->data['pesan'] ?? '' }}</span>
                    <span class="mt-1 block text-[11px] text-zinc-400">{{ $n->created_at?->diffForHumans() }}</span>
                </span>
            </a>
            @empty
            <div class="px-4 py-10 text-center">
                <x-heroicon-o-bell-slash class="mx-auto h-8 w-8 text-zinc-300" />
                <p class="mt-2 text-sm font-semibold text-zinc-700">Belum ada notifikasi</p>
                <p class="mt-0.5 text-xs text-zinc-500">Pembaruan penting akan muncul di sini.</p>
            </div>
            @endforelse
        </div>
        <a href="{{ route('notifikasi.index') }}" class="flex min-h-11 items-center justify-center gap-1.5 border-t border-zinc-100 bg-zinc-50 text-xs font-bold text-brand-700 hover:bg-brand-50">
            Lihat semua notifikasi <x-heroicon-m-arrow-right class="h-3.5 w-3.5" />
        </a>
    </section>
</div>
