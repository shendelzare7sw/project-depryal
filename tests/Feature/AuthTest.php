<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('tamu diarahkan ke halaman masuk ketika mengakses beranda', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('halaman masuk dapat ditampilkan', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('SIKASET');
});

test('pengguna dapat masuk menggunakan username', function () {
    $user = User::factory()->create([
        'username' => 'petugas1',
        'password' => bcrypt('password123'),
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'petugas1',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('pengguna dapat masuk menggunakan email', function () {
    $user = User::factory()->create([
        'email' => 'admin@batuceper.go.id',
        'password' => bcrypt('password123'),
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'admin@batuceper.go.id',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('masuk gagal jika kata sandi salah', function () {
    User::factory()->create([
        'username' => 'petugas2',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => 'petugas2',
        'password' => 'salah123',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('pengguna nonaktif ditolak masuk', function () {
    User::factory()->create([
        'username' => 'nonaktif',
        'password' => bcrypt('password123'),
        'is_active' => false,
    ]);

    $response = $this->post('/login', [
        'login' => 'nonaktif',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('login');
    $this->assertGuest();
});

test('pengguna dapat keluar dan kembali ke halaman masuk', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
