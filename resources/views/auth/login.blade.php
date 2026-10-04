<x-layouts.guest title="Masuk">
    <div x-data="{ busy: false }">
        <x-auth.header title="Masuk ke akun Anda" subtitle="Gunakan username atau email yang terdaftar." />

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" @submit="busy = true">
            @csrf
            <x-auth.input name="login" label="Username atau email" icon="user" required autofocus autocomplete="username" placeholder="Masukkan username atau email" />
            <div>
                <x-auth.input name="password" label="Kata sandi" icon="lock-closed" type="password" required autocomplete="current-password" placeholder="••••••••" />
                <a href="{{ route('password.request') }}" class="mt-1 inline-flex min-h-10 items-center text-xs font-bold text-brand-700 hover:underline">Lupa kata sandi?</a>
            </div>
            @if (config('services.turnstile.site_key'))
            <div>
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="id" data-theme="light" data-size="flexible"></div>
                @error('cf-turnstile-response')<p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600"><x-heroicon-m-exclamation-circle class="h-4 w-4" /> {{ $message }}</p>@enderror
            </div>
            @endif
            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-600">
                <input id="remember" name="remember" type="checkbox" class="checkbox checkbox-sm checkbox-primary rounded-md">
                Ingat saya di perangkat ini
            </label>
            <x-auth.submit>Masuk ke Sistem</x-auth.submit>
        </form>

        <p class="mt-10 text-center text-xs text-zinc-400">© {{ date('Y') }} SIKASET · Pemerintah Kecamatan Batuceper</p>
    </div>
    <x-flash />
</x-layouts.guest>
