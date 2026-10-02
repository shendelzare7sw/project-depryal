<x-layouts.app title="Notifikasi" subtitle="Pembaruan penting untuk akun Anda">
    @php
        $tones = [
            'success' => 'bg-emerald-50 text-emerald-600', 'warning' => 'bg-amber-50 text-amber-600',
            'error' => 'bg-rose-50 text-rose-600', 'info' => 'bg-sky-50 text-sky-600', 'primary' => 'bg-brand-50 text-brand-600',
        ];
        $belum = auth()->user()->unreadNotifications()->count();
    @endphp
    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><x-heroicon-o-bell class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Semua notifikasi</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $notifikasi->total() }} notifikasi · {{ $belum }} belum dibaca</p>
                </div>
            </div>
            @if ($belum > 0)
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf
                <x-ui.btn type="submit" tone="soft-brand" icon="check-circle" class="w-full">Tandai semua dibaca</x-ui.btn>
            </form>
            @endif
        </header>
        <div class="divide-y divide-zinc-100">
            @forelse ($notifikasi as $n)
            <a href="{{ route('notifikasi.baca', $n->id) }}" @class(['flex gap-3 p-4 transition hover:bg-zinc-50 sm:px-5', 'bg-brand-50/40' => $n->read_at === null])>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$n->data['tone'] ?? 'primary'] ?? $tones['primary'] }}">
                    <x-dynamic-component :component="'heroicon-o-'.($n->data['icon'] ?? 'bell')" class="h-5 w-5" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2">
                        <span class="truncate text-sm font-bold text-zinc-900">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
                        @if ($n->read_at === null)<x-ui.badge tone="error">Baru</x-ui.badge>@endif
                    </span>
                    <span class="mt-0.5 block text-xs leading-5 text-zinc-600">{{ $n->data['pesan'] ?? '' }}</span>
                    <span class="mt-1 block text-[11px] text-zinc-400">{{ $n->created_at?->translatedFormat('d F Y, H:i') }} · {{ $n->created_at?->diffForHumans() }}</span>
                </span>
                <x-heroicon-m-chevron-right class="mt-3 h-4 w-4 shrink-0 text-zinc-300" />
            </a>
            @empty
            <x-ui.empty-state icon="bell-slash" title="Belum ada notifikasi" text="Kejadian penting seperti import data, perhitungan MOORA, dan finalisasi periode akan muncul di sini." />
            @endforelse
        </div>
        @if ($notifikasi->hasPages())
        <footer class="border-t border-zinc-200/80 px-4 py-3">{{ $notifikasi->links() }}</footer>
        @endif
    </section>
</x-layouts.app>
