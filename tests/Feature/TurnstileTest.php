<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Support\Facades\Http;

function aktifkanTurnstile(): void
{
    config(['services.turnstile.site_key' => 'site-uji', 'services.turnstile.secret_key' => 'rahasia-uji']);
}

test('halaman login menampilkan widget Turnstile dan tidak lagi menampilkan akun demo', function () {
    aktifkanTurnstile();

    $this->get(route('login'))->assertOk()
        ->assertSee('class="cf-turnstile"', false)->assertSee('data-sitekey="site-uji"', false)
        ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false)
        ->assertDontSee('Akun demo')->assertDontSee('Kata sandi semua akun demo');
});

test('login berhasil bila token Turnstile valid', function () {
    aktifkanTurnstile();
    Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);
    $user = userWithRole(UserRole::Operator);

    $this->post(route('login'), ['login' => $user->username, 'password' => 'password', 'cf-turnstile-response' => 'token-ok'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    Http::assertSent(fn ($r) => $r['secret'] === 'rahasia-uji' && $r['response'] === 'token-ok');
});

test('login ditolak bila token Turnstile tidak ada atau tidak valid', function () {
    aktifkanTurnstile();
    Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);
    $user = userWithRole(UserRole::Operator);

    $this->post(route('login'), ['login' => $user->username, 'password' => 'password'])
        ->assertSessionHasErrors('cf-turnstile-response');
    $this->post(route('login'), ['login' => $user->username, 'password' => 'password', 'cf-turnstile-response' => 'palsu'])
        ->assertSessionHasErrors('cf-turnstile-response');

    $this->assertGuest();
});

test('Turnstile nonaktif bila kunci tidak dikonfigurasi', function () {
    config(['services.turnstile.site_key' => null, 'services.turnstile.secret_key' => null]);
    Http::fake();
    $user = userWithRole(UserRole::Pimpinan);

    $this->get(route('login'))->assertDontSee('cf-turnstile', false);
    $this->post(route('login'), ['login' => $user->username, 'password' => 'password'])->assertRedirect(route('dashboard'));

    Http::assertNothingSent();
});
