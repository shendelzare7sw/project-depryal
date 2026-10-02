{{-- Menu pengguna topbar (profil, keluar). Ponsel: panel selebar layar. --}}
@php($user = auth()->user())
<div class="sm:relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Menu pengguna"
        class="flex h-11 items-center gap-2 rounded-xl border border-zinc-200 bg-white py-1.5 pl-1.5 pr-1.5 transition hover:border-brand-500 md:pr-3">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-600 to-brand-800 text-xs font-bold text-white">
            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
        </span>
        <span class="hidden max-w-[10rem] text-left md:block">
            <span class="block truncate text-xs font-bold leading-tight text-zinc-900">{{ $user->name }}</span>
            <span class="block truncate text-[11px] leading-tight text-zinc-500">{{ $user->role->label() }}</span>
        </span>
        <x-heroicon-m-chevron-down class="hidden h-4 w-4 text-zinc-400 transition md:block" ::class="open && 'rotate-180'" />
    </button>
    <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
        class="fixed inset-x-4 top-[4.5rem] z-50 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-2xl shadow-zinc-900/10 sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-64">
        <div class="flex items-center gap-3 px-3 py-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-sm font-bold text-white">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-zinc-900">{{ $user->name }}</p>
                <p class="truncate text-xs text-zinc-500">{{ '@'.$user->username }} · {{ $user->role->label() }}</p>
            </div>
        </div>
        <div class="my-1 h-px bg-zinc-100"></div>
        <a href="{{ route('profil.edit') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
            <x-heroicon-o-user-circle class="h-5 w-5 text-zinc-400" /> Profil &amp; Kata Sandi
        </a>
        <a href="{{ route('notifikasi.index') }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
            <x-heroicon-o-bell class="h-5 w-5 text-zinc-400" /> Notifikasi
        </a>
        <x-confirm-form :action="route('logout')" title="Keluar dari sistem?" text="Sesi login Anda akan diakhiri." confirm="Ya, keluar" icon="question" class="mt-1 border-t border-zinc-100 pt-1">
            <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-sm font-bold text-rose-600 hover:bg-rose-50">
                <x-heroicon-o-arrow-right-start-on-rectangle class="h-5 w-5" /> Keluar
            </button>
        </x-confirm-form>
    </div>
</div>
