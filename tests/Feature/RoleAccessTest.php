<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('admin dapat mengakses halaman pengguna dan pengaturan', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    $this->actingAs($admin)->get('/pengguna')->assertOk();
    $this->actingAs($admin)->get('/pengaturan')->assertOk();
});

test('operator dan pimpinan ditolak 403 saat mengakses halaman pengguna', function () {
    $operator = User::factory()->create([
        'role' => UserRole::Operator,
        'is_active' => true,
    ]);
    $pimpinan = User::factory()->create([
        'role' => UserRole::Pimpinan,
        'is_active' => true,
    ]);

    $this->actingAs($operator)->get('/pengguna')->assertForbidden();
    $this->actingAs($pimpinan)->get('/pengguna')->assertForbidden();
});

test('semua role aktif dapat mengakses dashboard dan data aset', function () {
    foreach ([UserRole::Admin, UserRole::Operator, UserRole::Pimpinan] as $role) {
        $user = User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->actingAs($user)->get('/aset')->assertOk();
    }
});

test('pengguna nonaktif diblokir dari dashboard dan diarahkan ke login', function () {
    $inactive = User::factory()->create([
        'role' => UserRole::Operator,
        'is_active' => false,
    ]);

    $response = $this->actingAs($inactive)->get('/dashboard');

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
