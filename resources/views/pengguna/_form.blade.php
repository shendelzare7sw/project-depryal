{{-- Variabel: $user, $action, $method --}}
@php($diriSendiri = $user->exists && $user->is(auth()->user()))
<form method="POST" action="{{ $action }}" x-data="{ busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex items-center gap-3 p-4 sm:p-5">
            <a href="{{ route('pengguna.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
            <div class="min-w-0">
                <h2 class="text-base font-extrabold text-zinc-900">{{ $user->exists ? 'Perbarui akun' : 'Akun baru' }}</h2>
                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $user->exists ? '@'.$user->username.' · password diubah lewat Reset Password' : 'Username dipakai untuk login' }}</p>
            </div>
        </header>
        @if ($diriSendiri)
        <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 p-3 text-xs text-sky-900 sm:mx-5">
            <x-heroicon-o-information-circle class="h-5 w-5 shrink-0 text-sky-600" />
            <p>Ini akun Anda sendiri — peran dan status aktif tidak dapat diubah dari sini.</p>
        </div>
        @endif
    </section>

    <div class="grid min-w-0 gap-5 xl:grid-cols-2">
        <x-ui.card title="Identitas" icon="identification">
            <x-form.input name="name" label="Nama lengkap" :value="$user->name" required maxlength="255" />
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input name="username" label="Username" :value="$user->username" required maxlength="50" placeholder="mis. operator2" />
                <x-form.input name="email" label="Email" type="email" :value="$user->email" hint="Opsional" />
                <x-form.input name="nip" label="NIP" :value="$user->nip" maxlength="30" hint="Opsional" />
                <x-form.input name="jabatan" label="Jabatan" :value="$user->jabatan" maxlength="100" hint="Opsional" />
            </div>
        </x-ui.card>

        <x-ui.card title="Akses" icon="shield-check" icon-tone="text-violet-500">
            @php($roleTerpilih = old('role', $user->role?->value))
            <fieldset @disabled($diriSendiri)>
                <legend class="mb-1.5 text-sm font-semibold text-zinc-700">Peran <span class="text-rose-500">*</span></legend>
                <div class="space-y-2">
                    @foreach (\App\Enums\UserRole::cases() as $r)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                        <input type="radio" name="role" value="{{ $r->value }}" class="radio radio-sm radio-primary" @checked($roleTerpilih === $r->value) required>
                        <span class="text-sm font-semibold text-zinc-800">{{ $r->label() }}</span>
                    </label>
                    @endforeach
                </div>
            </fieldset>
            @if ($diriSendiri)<input type="hidden" name="role" value="{{ $user->role->value }}">@endif
            @error('role')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror

            <label class="flex min-h-[3.25rem] cursor-pointer items-center justify-between gap-3 rounded-xl border border-zinc-200 px-4">
                <span><span class="block text-sm font-bold text-zinc-800">Akun aktif</span><span class="block text-xs text-zinc-500">Akun nonaktif tidak dapat login</span></span>
                <input type="hidden" name="is_active" value="{{ $diriSendiri ? 1 : 0 }}">
                <input type="checkbox" name="is_active" value="1" class="toggle toggle-success" @checked(old('is_active', $user->is_active)) @disabled($diriSendiri)>
            </label>
            @error('is_active')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror

            @unless ($user->exists)
            <div class="grid grid-cols-1 gap-4 border-t border-zinc-100 pt-3 sm:grid-cols-2">
                <x-form.input name="password" label="Password awal" type="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-form.input name="password_confirmation" label="Ulangi password" type="password" required autocomplete="new-password" />
            </div>
            @endunless
        </x-ui.card>
    </div>

    <x-ui.action-bar>
        <div class="flex items-center justify-end gap-2">
            <x-ui.btn tone="white" :href="route('pengguna.index')">Batal</x-ui.btn>
            <x-ui.btn type="submit" tone="primary" icon="check" class="flex-1 sm:flex-none" ::disabled="busy">Simpan</x-ui.btn>
        </div>
    </x-ui.action-bar>
</form>
