<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('akun dengan kata sandi sementara diarahkan ke halaman ganti kata sandi', function () {
    $user = User::factory()->create(['role' => UserRole::Operator, 'must_change_password' => true, 'password' => 'sementara1']);

    $this->actingAs($user)->get(route('aset.index'))->assertRedirect(route('password.ganti'));
    $this->get(route('password.ganti'))->assertOk()->assertSee('Ganti kata sandi Anda');

    $this->put(route('password.ganti.update'), ['password' => 'sementara1', 'password_confirmation' => 'sementara1'])
        ->assertSessionHasErrors('password');
    $this->put(route('password.ganti.update'), ['password' => 'rahasiaBaru9', 'password_confirmation' => 'rahasiaBaru9'])
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('rahasiaBaru9', $user->fresh()->password))->toBeTrue();
    $this->get(route('aset.index'))->assertOk();
    $this->get(route('password.ganti'))->assertRedirect(route('dashboard'));
});

test('akun baru dan reset password oleh admin wajib ganti kata sandi', function () {
    $admin = userWithRole(UserRole::Admin);
    $this->actingAs($admin)->post(route('pengguna.store'), [
        'name' => 'Staf Baru', 'username' => 'staf.baru', 'role' => UserRole::Operator->value, 'is_active' => '1',
        'password' => 'awalan123', 'password_confirmation' => 'awalan123',
    ])->assertSessionHasNoErrors();
    expect(User::where('username', 'staf.baru')->value('must_change_password'))->toBeTrue();

    $lama = userWithRole(UserRole::Pimpinan);
    $this->post(route('pengguna.reset-password', $lama))->assertSessionHas('password_baru');
    expect($lama->fresh()->must_change_password)->toBeTrue();
});

test('lupa kata sandi mengirim tautan hanya ke akun aktif tanpa membocorkan keberadaan email', function () {
    Notification::fake();
    $aktif = User::factory()->create(['email' => 'aktif@batuceper.go.id', 'is_active' => true]);
    $nonaktif = User::factory()->create(['email' => 'mati@batuceper.go.id', 'is_active' => false]);

    foreach (['aktif@batuceper.go.id', 'mati@batuceper.go.id', 'tidakada@batuceper.go.id'] as $email) {
        $this->from(route('password.request'))->post(route('password.email'), ['email' => $email])
            ->assertRedirect(route('password.request'))->assertSessionHas('status');
    }

    Notification::assertSentTo($aktif, ResetPassword::class);
    Notification::assertNotSentTo($nonaktif, ResetPassword::class);
});

test('tautan reset mengganti kata sandi dan menolak token tidak valid', function () {
    $user = User::factory()->create(['email' => 'ops@batuceper.go.id', 'must_change_password' => true]);
    $token = Password::createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk()->assertSee('Buat kata sandi baru');

    $this->post(route('password.update'), ['token' => 'salah', 'email' => $user->email, 'password' => 'kataBaru123', 'password_confirmation' => 'kataBaru123'])
        ->assertSessionHasErrors('email');

    $this->post(route('password.update'), ['token' => $token, 'email' => $user->email, 'password' => 'kataBaru123', 'password_confirmation' => 'kataBaru123'])
        ->assertRedirect(route('login'));

    expect(Hash::check('kataBaru123', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->must_change_password)->toBeFalse();
});

test('halaman masuk menautkan lupa kata sandi', function () {
    $this->get(route('login'))->assertOk()->assertSee(route('password.request'));
    $this->get(route('password.request'))->assertOk()->assertSee('Lupa kata sandi?');
});
