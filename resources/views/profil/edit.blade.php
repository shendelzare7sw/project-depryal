<x-layouts.app title="Profil Saya" subtitle="Informasi akun dan kata sandi">
    <section class="relative min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm shadow-zinc-900/[0.03] sm:p-5">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-xl font-extrabold text-white">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <h2 class="truncate text-base font-extrabold text-zinc-900 sm:text-lg">{{ $user->name }}</h2>
                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ '@'.$user->username }}{{ $user->email ? ' · '.$user->email : '' }}</p>
                <div class="mt-2"><x-ui.badge :tone="$user->role->color()">{{ $user->role->label() }}</x-ui.badge></div>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('profil.update') }}" x-data="{ busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="grid min-w-0 gap-5 xl:grid-cols-2">
            <x-ui.card title="Informasi akun" icon="user-circle">
                <x-form.input name="name" label="Nama lengkap" :value="$user->name" required />
                <x-form.input name="email" label="Alamat email" type="email" :value="$user->email" required />
            </x-ui.card>
            <x-ui.card title="Ganti kata sandi" icon="key" icon-tone="text-amber-500" subtitle="Kosongkan bila tidak ingin mengubah">
                <x-form.input name="current_password" label="Kata sandi saat ini" type="password" autocomplete="current-password" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="password" label="Kata sandi baru" type="password" placeholder="Minimal 8 karakter" autocomplete="new-password" />
                    <x-form.input name="password_confirmation" label="Ulangi kata sandi baru" type="password" autocomplete="new-password" />
                </div>
            </x-ui.card>
        </div>
        <div class="flex justify-end">
            <x-ui.btn type="submit" tone="primary" icon="check" class="w-full sm:w-auto" ::disabled="busy">Simpan Perubahan</x-ui.btn>
        </div>
    </form>
</x-layouts.app>
