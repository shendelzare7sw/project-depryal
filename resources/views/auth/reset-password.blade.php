<x-layouts.guest title="Atur Ulang Kata Sandi">
    <div x-data="{ busy: false }">
        <x-auth.header title="Buat kata sandi baru" subtitle="Minimal 8 karakter. Setelah disimpan, masuk dengan kata sandi baru." />

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4" @submit="busy = true">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-auth.input name="email" label="Email" icon="envelope" type="email" :value="$email" required autocomplete="email" />
            <x-auth.input name="password" label="Kata sandi baru" icon="lock-closed" type="password" required autofocus autocomplete="new-password" />
            <x-auth.input name="password_confirmation" label="Ulangi kata sandi baru" icon="lock-closed" type="password" required autocomplete="new-password" />
            <x-auth.submit>Simpan Kata Sandi</x-auth.submit>
        </form>
    </div>
</x-layouts.guest>
