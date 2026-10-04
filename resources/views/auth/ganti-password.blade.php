<x-layouts.guest title="Ganti Kata Sandi">
    <div x-data="{ busy: false }">
        <x-auth.header title="Ganti kata sandi Anda" subtitle="Demi keamanan, kata sandi awal/sementara wajib diganti sebelum memakai aplikasi." />

        <form method="POST" action="{{ route('password.ganti.update') }}" class="mt-6 space-y-4" @submit="busy = true">
            @csrf
            @method('PUT')
            <x-auth.input name="password" label="Kata sandi baru" icon="lock-closed" type="password" required autofocus autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-auth.input name="password_confirmation" label="Ulangi kata sandi baru" icon="lock-closed" type="password" required autocomplete="new-password" />
            <x-auth.submit>Simpan &amp; Lanjutkan</x-auth.submit>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-zinc-500 hover:text-zinc-800">
                <x-heroicon-o-arrow-left-start-on-rectangle class="h-4 w-4" /> Keluar
            </button>
        </form>
    </div>
    <x-flash />
</x-layouts.guest>
