<x-layouts.app title="Profil Saya">
    <x-ui.page-header title="Profil Saya" subtitle="Kelola informasi akun dan kata sandi" />

    <div class="max-w-2xl">
        <x-ui.card>
            <form method="POST" action="{{ route('profil.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-form.input name="name" label="Nama Lengkap" :value="$user->name" required />
                <x-form.input name="email" label="Alamat Email" type="email" :value="$user->email" required />

                <div class="divider text-xs text-base-content/40">Ganti Kata Sandi (Opsional)</div>

                <x-form.input name="current_password" label="Kata Sandi Saat Ini" type="password"
                    placeholder="Kosongkan jika tidak ingin mengubah" />
                <x-form.input name="password" label="Kata Sandi Baru" type="password"
                    placeholder="Minimal 8 karakter" />
                <x-form.input name="password_confirmation" label="Konfirmasi Kata Sandi Baru" type="password"
                    placeholder="Ulangi kata sandi baru" />

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn btn-primary">
                        <x-heroicon-o-check class="w-4 h-4" /> Simpan Perubahan
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
