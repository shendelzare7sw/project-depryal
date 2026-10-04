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
                <x-form.input name="email" label="Alamat email" type="email" :value="$user->email" hint="Opsional" />
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

    @if (\App\Support\Integrasi::telegramAktif())
    @php($tautan = session('telegram_tautan'))
    <x-ui.card title="Notifikasi Telegram" icon="paper-airplane" icon-tone="text-sky-500" subtitle="Terima pemberitahuan penting langsung di Telegram">
        @if ($user->telegram_chat_id)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-center gap-2 text-sm font-semibold text-emerald-700"><x-heroicon-o-check-circle class="h-5 w-5" /> Telegram terhubung</p>
            <x-confirm-form :action="route('profil.telegram.putus')" method="DELETE" title="Putuskan Telegram?" text="Notifikasi tidak lagi dikirim ke Telegram Anda." confirm="Ya, putuskan">
                <x-ui.btn type="submit" tone="soft-rose" icon="x-mark" class="w-full">Putuskan</x-ui.btn>
            </x-confirm-form>
        </div>
        @elseif ($tautan)
        <ol class="list-decimal space-y-1 pl-5 text-sm leading-6 text-zinc-700">
            <li>Tekan <strong>Buka Telegram</strong>, lalu tekan tombol <strong>Start</strong> di percakapan bot.</li>
            <li>Kembali ke halaman ini dan tekan <strong>Saya sudah menekan Start</strong>.</li>
        </ol>
        <div class="grid grid-cols-1 gap-2 sm:flex">
            <x-ui.btn tone="soft-sky" icon="arrow-top-right-on-square" :href="$tautan['tautan']" target="_blank" rel="noopener">Buka Telegram</x-ui.btn>
            <x-confirm-form :action="route('profil.telegram.cek')" :when="false">
                <x-ui.btn type="submit" tone="primary" icon="check" class="w-full">Saya sudah menekan Start</x-ui.btn>
            </x-confirm-form>
        </div>
        @else
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-600">Belum terhubung. Cukup dua langkah, tanpa mengetik nomor apa pun.</p>
            <x-confirm-form :action="route('profil.telegram.mulai')" :when="false">
                <x-ui.btn type="submit" tone="primary" icon="link" class="w-full">Hubungkan Telegram</x-ui.btn>
            </x-confirm-form>
        </div>
        @endif
    </x-ui.card>
    @endif
</x-layouts.app>
