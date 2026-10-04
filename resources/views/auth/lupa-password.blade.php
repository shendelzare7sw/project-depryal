<x-layouts.guest title="Lupa Kata Sandi">
    <div x-data="{ busy: false }">
        <x-auth.header title="Lupa kata sandi?" subtitle="Masukkan email akun Anda. Kami kirimkan tautan untuk membuat kata sandi baru (berlaku 60 menit)." />

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" @submit="busy = true">
            @csrf
            <x-auth.input name="email" label="Email" icon="envelope" type="email" required autofocus autocomplete="email" placeholder="nama@batuceper.go.id" />
            <x-auth.submit>Kirim Tautan Reset</x-auth.submit>
        </form>

        <div class="mt-6 rounded-xl bg-zinc-100 p-3 text-xs leading-5 text-zinc-600">
            Akun tidak memiliki email? Hubungi <strong>Administrator</strong> untuk mereset kata sandi Anda.
        </div>
        <a href="{{ route('login') }}" class="mt-4 inline-flex min-h-[2.75rem] items-center gap-1.5 text-sm font-bold text-brand-700 hover:underline">
            <x-heroicon-m-arrow-left class="h-4 w-4" /> Kembali ke halaman masuk
        </a>
    </div>
</x-layouts.guest>
