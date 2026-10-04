<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Providers\AppServiceProvider;
use App\Support\Setting;

test('halaman aplikasi memuat tombol perbesar teks dan logout otomatis sesuai pengaturan', function () {
    $this->get(route('login'))->assertSee('aria-label="Perbesar teks"', false);
    $user = userWithRole(UserRole::Operator);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()->assertSee('aria-label="Perbesar teks"', false)->assertSee('30 * 60000', false);

    Setting::set('batas_idle_menit', '0');
    $this->get(route('dashboard'))->assertDontSee('sikaset-aktif', false);
});

test('logout karena tidak aktif memberi pesan penjelasan', function () {
    $this->actingAs(userWithRole(UserRole::Pimpinan))->post(route('logout'), ['alasan' => 'tidak-aktif'])
        ->assertRedirect(route('login'))->assertSessionHas('error', fn (string $p) => str_contains($p, 'tidak ada aktivitas'));
    $this->assertGuest();
});

test('admin mengatur batas logout otomatis dan sesi server mengikutinya', function () {
    $this->actingAs(userWithRole(UserRole::Admin))->put(route('pengaturan.update'), dataPengaturan(['batas_idle_menit' => '60']))
        ->assertSessionHasNoErrors();
    expect(Setting::batasIdleMenit())->toBe(60);

    $this->put(route('pengaturan.update'), dataPengaturan(['batas_idle_menit' => '7']))->assertSessionHasErrors('batas_idle_menit');

    (new AppServiceProvider(app()))->boot();
    expect(config('session.lifetime'))->toBe(65);
});
