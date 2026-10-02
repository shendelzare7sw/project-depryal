<x-layouts.guest title="Masuk">
    <div x-data="{ login: @js(old('login', '')), password: '', lihat: false, busy: false }">
        {{-- Identitas (ponsel) --}}
        <div class="mb-8 flex items-center gap-3 lg:hidden">
            <img src="{{ asset('img/logo.jpeg') }}" alt="Logo Kecamatan Batuceper" class="h-12 w-12 rounded-xl bg-white object-contain p-0.5 ring-1 ring-zinc-200">
            <div>
                <p class="text-lg font-extrabold tracking-tight text-zinc-900">SIKASET</p>
                <p class="text-xs text-zinc-500">SPK Kelayakan Aset BMD · Kec. Batuceper</p>
            </div>
        </div>

        <h2 class="text-2xl font-extrabold tracking-tight text-zinc-900">Masuk ke akun Anda</h2>
        <p class="mt-1.5 text-sm text-zinc-500">Gunakan username atau email yang terdaftar.</p>

        @if ($errors->any())
        <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-800">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" /> {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" @submit="busy = true">
            @csrf
            <div>
                <label for="login" class="mb-1.5 block text-sm font-semibold text-zinc-700">Username atau email</label>
                <div class="relative">
                    <x-heroicon-o-user class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-zinc-400" />
                    <input id="login" name="login" type="text" x-model="login" required autofocus autocomplete="username" placeholder="mis. operator"
                        class="h-12 w-full rounded-xl border border-zinc-300 bg-white pl-11 pr-3 text-sm outline-none placeholder:text-zinc-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-600/10">
                </div>
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-zinc-700">Kata sandi</label>
                <div class="relative">
                    <x-heroicon-o-lock-closed class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-zinc-400" />
                    <input id="password" name="password" :type="lihat ? 'text' : 'password'" x-model="password" required autocomplete="current-password" placeholder="••••••••"
                        class="h-12 w-full rounded-xl border border-zinc-300 bg-white pl-11 pr-12 text-sm outline-none placeholder:text-zinc-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-600/10">
                    <button type="button" @click="lihat = !lihat" class="absolute right-1.5 top-1.5 flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700" :aria-label="lihat ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <x-heroicon-o-eye class="h-5 w-5" x-show="!lihat" />
                        <x-heroicon-o-eye-slash class="h-5 w-5" x-show="lihat" x-cloak />
                    </button>
                </div>
            </div>
            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-600">
                <input id="remember" name="remember" type="checkbox" class="checkbox checkbox-sm checkbox-primary rounded-md">
                Ingat saya di perangkat ini
            </label>
            <button type="submit" :disabled="busy" class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-700 text-sm font-bold text-white shadow-lg shadow-brand-900/20 transition hover:bg-brand-800 disabled:opacity-60">
                <span class="loading loading-spinner loading-sm" x-show="busy" x-cloak></span>
                Masuk ke Sistem
                <x-heroicon-m-arrow-right class="h-4 w-4" x-show="!busy" />
            </button>
        </form>

        {{-- Akun demo --}}
        <div class="mt-8">
            <p class="mb-3 flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                <span class="h-px flex-1 bg-zinc-200"></span> Akun demo · klik untuk isi <span class="h-px flex-1 bg-zinc-200"></span>
            </p>
            <div class="grid grid-cols-3 gap-2">
                @foreach ([['admin', 'Admin', 'Sistem', 'shield-check', 'bg-violet-50 text-violet-700'], ['operator', 'Operator', 'Pengurus barang', 'building-office-2', 'bg-brand-50 text-brand-700'], ['pimpinan', 'Pimpinan', 'Camat', 'check-badge', 'bg-amber-50 text-amber-700']] as [$u, $label, $ket, $icon, $tone])
                <button type="button" @click="login = '{{ $u }}'; password = 'password'"
                    class="group flex flex-col items-start gap-2 rounded-xl border border-zinc-200 bg-white p-3 text-left transition hover:-translate-y-0.5 hover:border-brand-400 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                    :class="login === '{{ $u }}' && 'border-brand-600 ring-2 ring-brand-600/15'">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $tone }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4" /></span>
                    <span class="min-w-0">
                        <span class="block text-xs font-extrabold text-zinc-900">{{ $label }}</span>
                        <span class="block truncate text-[10px] text-zinc-500">{{ $ket }}</span>
                    </span>
                </button>
                @endforeach
            </div>
            <p class="mt-2 text-center text-[11px] text-zinc-400">Kata sandi semua akun demo: <span class="font-mono font-semibold text-zinc-600">password</span></p>
        </div>

        <p class="mt-10 text-center text-xs text-zinc-400">© {{ date('Y') }} SIKASET · Pemerintah Kecamatan Batuceper</p>
    </div>
    <x-flash />
</x-layouts.guest>
