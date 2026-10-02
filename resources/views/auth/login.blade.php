<x-layouts.guest title="Masuk">
    <div x-data="{ login: '{{ old('login', '') }}', password: '' }" class="card bg-base-100 shadow-xl border border-base-300">
        <div class="card-body p-6 sm:p-8 gap-4">
            {{-- Brand / Header --}}
            <div class="text-center">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Logo Batuceper"
                    class="w-16 h-16 mx-auto mb-2 object-contain drop-shadow-sm rounded-lg">
                <h1 class="text-2xl font-black tracking-tight text-primary">SIKASET</h1>
                <p class="text-xs uppercase tracking-wider font-semibold text-base-content/70 mt-0.5">
                    SPK Kelayakan Aset BMD
                </p>
                <p class="text-xs text-base-content/50">Kecamatan Batuceper, Kota Tangerang</p>
            </div>

            <x-flash />

            <form method="POST" action="{{ route('login') }}" class="space-y-4 mt-1">
                @csrf
                <x-form.input name="login" label="Username atau Email" type="text"
                    x-model="login"
                    placeholder="admin, operator, atau email@batuceper.go.id" required />
                <x-form.input name="password" label="Kata Sandi" type="password"
                    x-model="password"
                    placeholder="••••••••" required />

                <div class="flex items-center justify-between">
                    <label class="label cursor-pointer gap-2 py-0">
                        <input id="remember" name="remember" type="checkbox"
                            class="checkbox checkbox-primary checkbox-sm">
                        <span class="label-text text-sm">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full gap-2 text-white font-semibold">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                    Masuk ke Sistem
                </button>
            </form>

            <div class="divider my-1 text-xs text-base-content/40 font-medium">Akun Demo (Klik untuk Isi Cepat)</div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                <button type="button"
                    @click="login = 'admin'; password = 'password'"
                    class="bg-base-200 hover:bg-primary/10 hover:border-primary p-2.5 rounded-lg border border-base-300 transition-all text-left flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/50">
                    <div>
                        <span class="badge badge-primary badge-xs font-bold text-white mb-1">Admin</span>
                        <p class="font-bold text-base-content text-[11px] truncate">Administrator</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-base-300/60 font-mono text-[10px] text-base-content/70">
                        <p class="truncate font-semibold text-primary">admin</p>
                        <p class="text-base-content/50">password</p>
                    </div>
                </button>

                <button type="button"
                    @click="login = 'operator'; password = 'password'"
                    class="bg-base-200 hover:bg-secondary/10 hover:border-secondary p-2.5 rounded-lg border border-base-300 transition-all text-left flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-secondary/50">
                    <div>
                        <span class="badge badge-secondary badge-xs font-bold text-white mb-1">Operator</span>
                        <p class="font-bold text-base-content text-[11px] truncate">Pengurus Barang</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-base-300/60 font-mono text-[10px] text-base-content/70">
                        <p class="truncate font-semibold text-secondary">operator</p>
                        <p class="text-base-content/50">password</p>
                    </div>
                </button>

                <button type="button"
                    @click="login = 'pimpinan'; password = 'password'"
                    class="bg-base-200 hover:bg-accent/10 hover:border-accent p-2.5 rounded-lg border border-base-300 transition-all text-left flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/50">
                    <div>
                        <span class="badge badge-accent badge-xs font-bold text-white mb-1">Pimpinan</span>
                        <p class="font-bold text-base-content text-[11px] truncate">Camat</p>
                    </div>
                    <div class="mt-2 pt-1 border-t border-base-300/60 font-mono text-[10px] text-base-content/70">
                        <p class="truncate font-semibold text-accent-content">pimpinan</p>
                        <p class="text-base-content/50">password</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-base-content/50 mt-4">
        © {{ date('Y') }} SIKASET — Pemerintah Kecamatan Batuceper
    </p>
</x-layouts.guest>
