<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Pengaturan;
use App\Notifications\SistemNotification;
use App\Services\Notifikasi;
use App\Support\Integrasi;
use App\Support\Setting;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\Models\Activity;

const TOKEN_BOT = '123456789:AAHabcdefghijklmnopqrstuvwxyz012345';

function aktifkanTelegram(): void
{
    Setting::set('telegram_bot_token', Crypt::encryptString(TOKEN_BOT));
    Setting::set('telegram_bot_username', 'SikasetBot');
}

test('admin menyimpan token bot: diverifikasi ke Telegram, disimpan terenkripsi, tidak tercatat di audit', function () {
    Http::fake(['api.telegram.org/*/getMe' => Http::response(['ok' => true, 'result' => ['username' => 'SikasetBot']])]);
    $admin = userWithRole(UserRole::Admin);

    $this->actingAs($admin)->get(route('integrasi.index'))->assertOk()->assertSee('Telegram Bot');
    $this->put(route('integrasi.update'), ['telegram_bot_token' => TOKEN_BOT, 'smtp_port' => 587])
        ->assertRedirect(route('integrasi.index'))->assertSessionHas('success');

    expect(Pengaturan::where('key', 'telegram_bot_token')->value('value'))->not->toContain(TOKEN_BOT)
        ->and(Integrasi::telegramToken())->toBe(TOKEN_BOT)
        ->and(Integrasi::telegramBot())->toBe('SikasetBot')
        ->and(Activity::all()->contains(fn ($a) => str_contains(json_encode($a->properties), 'AAHabc')))->toBeFalse();

    $this->get(route('integrasi.index'))->assertDontSee(TOKEN_BOT)->assertSee('@SikasetBot');

    // Kosong = tidak berubah; centang hapus = dihapus.
    $this->put(route('integrasi.update'), ['telegram_bot_token' => ''])->assertSessionHas('success');
    expect(Integrasi::telegramToken())->toBe(TOKEN_BOT);
    $this->put(route('integrasi.update'), ['hapus_telegram_bot_token' => '1'])->assertSessionHas('success');
    expect(Integrasi::telegramAktif())->toBeFalse();
});

test('token yang ditolak Telegram tidak disimpan', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Unauthorized'], 401)]);

    $this->actingAs(userWithRole(UserRole::Admin))->put(route('integrasi.update'), ['telegram_bot_token' => TOKEN_BOT])
        ->assertSessionHas('error', fn (string $pesan) => str_contains($pesan, 'Unauthorized') && ! str_contains($pesan, TOKEN_BOT));

    expect(Integrasi::terisi('telegram_bot_token'))->toBeFalse();
});

test('pengaturan SMTP dari menu menimpa konfigurasi email', function () {
    $this->actingAs(userWithRole(UserRole::Admin))->put(route('integrasi.update'), [
        'smtp_host' => 'smtp.gmail.com', 'smtp_port' => 465, 'smtp_enkripsi' => 'ssl', 'smtp_username' => 'kec@gmail.com',
        'smtp_password' => 'sandi-app', 'smtp_dari_alamat' => 'kec@gmail.com', 'notifikasi_email' => '1',
    ])->assertSessionHasNoErrors();

    Integrasi::terapkan();
    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.gmail.com')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.mailers.smtp.password'))->toBe('sandi-app')
        ->and(Integrasi::emailNotifikasiAktif())->toBeTrue();
});

test('hanya admin yang membuka menu integrasi', function (UserRole $role) {
    $this->actingAs(userWithRole($role))->get(route('integrasi.index'))->assertForbidden();
})->with([UserRole::Operator, UserRole::Pimpinan]);

test('pengguna menghubungkan Telegram dari profil tanpa mengetik chat ID', function () {
    aktifkanTelegram();
    $user = userWithRole(UserRole::Pimpinan);

    $this->actingAs($user)->post(route('profil.telegram.mulai'))->assertRedirect(route('profil.edit'));
    $kode = session('telegram_tautan.kode');
    $this->get(route('profil.edit'))->assertSee('https://t.me/SikasetBot?start='.$kode, false);

    Http::fake([
        'api.telegram.org/*/getUpdates' => Http::sequence()
            ->push(['ok' => true, 'result' => []])
            ->push(['ok' => true, 'result' => [['message' => ['text' => "/start {$kode}", 'chat' => ['id' => 987654]]]]]),
        'api.telegram.org/*/sendMessage' => Http::response(['ok' => true, 'result' => []]),
    ]);
    $this->post(route('profil.telegram.cek'))->assertSessionHas('error');
    expect($user->fresh()->telegram_chat_id)->toBeNull();

    $this->post(route('profil.telegram.cek'))->assertSessionHas('success');
    expect($user->fresh()->telegram_chat_id)->toBe('987654');

    $this->delete(route('profil.telegram.putus'));
    expect($user->fresh()->telegram_chat_id)->toBeNull();
});

test('notifikasi sistem diteruskan ke Telegram dan kegagalan gateway tidak menggagalkan proses', function () {
    $this->withoutDefer();
    aktifkanTelegram();
    $pimpinan = userWithRole(UserRole::Pimpinan);
    $pimpinan->update(['telegram_chat_id' => '555']);
    userWithRole(UserRole::Pimpinan);
    $notif = new SistemNotification('MOORA dihitung', 'Periode 2026 siap ditinjau', 'https://sikaset.test/x');

    Http::fake(['api.telegram.org/*/sendMessage' => Http::sequence()->push(['ok' => true, 'result' => []])->push('galat', 500)]);
    app(Notifikasi::class)->kirimKeRole([UserRole::Pimpinan], $notif);
    Http::assertSentCount(1);
    Http::assertSent(fn (Request $r) => $r['chat_id'] === '555' && str_contains($r['text'], 'MOORA dihitung'));
    expect($pimpinan->notifications()->count())->toBe(1);

    app(Notifikasi::class)->kirimKeRole([UserRole::Pimpinan], $notif);
    expect($pimpinan->notifications()->count())->toBe(2);
});
