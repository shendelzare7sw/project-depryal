<x-layouts.app title="Integrasi" subtitle="Notifikasi Telegram & email (SMTP)">
    @php
        $nilai = fn (string $k, mixed $default = '') => old($k, $pengaturan[$k] ?? $default);
        $bot = \App\Support\Integrasi::telegramBot();
        $telegramAktif = \App\Support\Integrasi::telegramAktif();
        $smtpAktif = \App\Support\Integrasi::smtpAktif();
        $adaToken = \App\Support\Integrasi::terisi('telegram_bot_token');
        $adaSandiSmtp = \App\Support\Integrasi::terisi('smtp_password');
    @endphp

    @if ($errors->any())
    <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
        <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
        <ul class="list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('integrasi.update') }}" x-data="{ busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
        @csrf
        @method('PUT')
        <div class="grid min-w-0 gap-5 xl:grid-cols-2">
            <x-ui.card title="Telegram Bot" icon="paper-airplane" icon-tone="text-sky-500" subtitle="Gratis · notifikasi langsung ke Telegram pengguna">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge :tone="$telegramAktif ? 'success' : 'ghost'" dot>{{ $telegramAktif ? 'Aktif' : 'Belum diatur' }}</x-ui.badge>
                    @if ($bot)<span class="font-mono text-xs text-zinc-600">{{ '@'.$bot }}</span>@endif
                </div>
                <ol class="list-decimal space-y-1 rounded-xl bg-sky-50 p-3 pl-7 text-xs leading-5 text-sky-900">
                    <li>Di Telegram, buka <strong>@BotFather</strong> → kirim <code>/newbot</code> → beri nama, mis. "SIKASET Batuceper".</li>
                    <li>Salin token yang diberikan, tempel di bawah, lalu Simpan.</li>
                    <li>Setiap pengguna menekan <strong>Hubungkan Telegram</strong> di menu Profil.</li>
                </ol>
                <x-form.rahasia name="telegram_bot_token" label="Token bot" :tersimpan="$adaToken" placeholder="123456789:AAH..." />
            </x-ui.card>

            <x-ui.card title="Email (SMTP)" icon="envelope" icon-tone="text-violet-500" subtitle="Untuk lupa kata sandi & salinan notifikasi">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge :tone="$smtpAktif ? 'success' : 'ghost'" dot>{{ $smtpAktif ? 'SMTP aktif' : 'Belum diatur' }}</x-ui.badge>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-[minmax(0,1fr)_7rem_8rem]">
                    <x-form.input name="smtp_host" label="Host SMTP" :value="$nilai('smtp_host')" placeholder="smtp.gmail.com" maxlength="255" />
                    <x-form.input name="smtp_port" label="Port" type="number" :value="$nilai('smtp_port', 587)" min="1" max="65535" />
                    <x-form.select name="smtp_enkripsi" label="Enkripsi" :options="['tls' => 'TLS (587)', 'ssl' => 'SSL (465)']" :value="$nilai('smtp_enkripsi', 'tls')" />
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="smtp_username" label="Username" :value="$nilai('smtp_username')" autocomplete="off" maxlength="255" />
                    <x-form.rahasia name="smtp_password" label="Kata sandi SMTP" :tersimpan="$adaSandiSmtp" placeholder="App Password" />
                    <x-form.input name="smtp_dari_alamat" label="Alamat pengirim" type="email" :value="$nilai('smtp_dari_alamat')" placeholder="sikaset@batuceper.go.id" maxlength="255" />
                    <x-form.input name="smtp_dari_nama" label="Nama pengirim" :value="$nilai('smtp_dari_nama', 'SIKASET')" maxlength="100" />
                </div>
                <label class="flex min-h-[3.25rem] cursor-pointer items-center justify-between gap-3 rounded-xl border border-zinc-200 px-4">
                    <span>
                        <span class="block text-sm font-bold text-zinc-800">Kirim salinan notifikasi ke email</span>
                        <span class="block text-xs text-zinc-500">Lupa kata sandi tetap memakai SMTP walau opsi ini mati</span>
                    </span>
                    <input type="hidden" name="notifikasi_email" value="0">
                    <input type="checkbox" name="notifikasi_email" value="1" class="toggle toggle-success" @checked($nilai('notifikasi_email') === '1' || $nilai('notifikasi_email') === true)>
                </label>
            </x-ui.card>
        </div>

        <p class="flex items-start gap-2 rounded-xl bg-zinc-100 p-3 text-xs leading-5 text-zinc-600">
            <x-heroicon-o-lock-closed class="h-4 w-4 shrink-0 text-zinc-500" />
            Token & kata sandi disimpan terenkripsi dan tidak pernah ditampilkan ulang; audit log hanya mencatat bahwa nilainya berubah.
            Kunci Cloudflare Turnstile (halaman masuk) tetap diatur di berkas .env server.
        </p>

        <x-ui.action-bar>
            <div class="flex items-center justify-between gap-2">
                <p class="hidden text-xs text-zinc-500 md:block">Setelah menyimpan, gunakan tombol uji di bawah.</p>
                <x-ui.btn type="submit" tone="primary" icon="check" class="w-full md:w-auto" ::disabled="busy">Simpan Integrasi</x-ui.btn>
            </div>
        </x-ui.action-bar>
    </form>

    <x-ui.card title="Uji pengiriman" icon="bolt" icon-tone="text-amber-500" subtitle="Pesan uji dikirim ke akun Anda sendiri">
        <div class="grid grid-cols-1 gap-2 sm:flex">
            <x-confirm-form :action="route('integrasi.uji', 'telegram')" :when="false">
                <x-ui.btn type="submit" tone="soft-sky" icon="paper-airplane" class="w-full" :disabled="! $telegramAktif">Uji Telegram</x-ui.btn>
            </x-confirm-form>
            <x-confirm-form :action="route('integrasi.uji', 'email')" :when="false">
                <x-ui.btn type="submit" tone="soft-brand" icon="envelope" class="w-full" :disabled="! $smtpAktif">Uji Email</x-ui.btn>
            </x-confirm-form>
        </div>
    </x-ui.card>
</x-layouts.app>
