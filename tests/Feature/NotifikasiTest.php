<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Notifications\SistemNotification;

function kirimNotifikasi($user, string $judul = 'Import data BMD selesai', ?string $url = null): void
{
    $user->notify(new SistemNotification($judul, '12 data aset diimpor.', $url ?? route('aset.index'), 'arrow-up-tray', 'success'));
}

test('lonceng topbar menampilkan jumlah belum dibaca dan notifikasi terbaru', function () {
    $user = userWithRole(UserRole::Admin);
    kirimNotifikasi($user, 'Periode difinalisasi');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()->assertSee('Buka notifikasi')->assertSee('Periode difinalisasi')->assertSee('1 belum dibaca');
});

test('membuka notifikasi menandainya dibaca lalu mengarah ke tautannya', function () {
    $user = userWithRole(UserRole::Operator);
    kirimNotifikasi($user, url: route('kriteria.index'));
    $id = $user->notifications()->value('id');

    $this->actingAs($user)->get(route('notifikasi.baca', $id))->assertRedirect(route('kriteria.index'));

    expect($user->unreadNotifications()->count())->toBe(0);
});

test('tandai semua dibaca dan halaman daftar notifikasi', function () {
    $user = userWithRole(UserRole::Pimpinan);
    kirimNotifikasi($user);
    kirimNotifikasi($user, 'Kedua');

    $this->actingAs($user)->get(route('notifikasi.index'))->assertOk()->assertSee('Kedua')->assertSee('2 belum dibaca');
    $this->actingAs($user)->post(route('notifikasi.baca-semua'))->assertSessionHas('success');

    expect($user->unreadNotifications()->count())->toBe(0);
});

test('pengguna tidak bisa membuka notifikasi milik orang lain', function () {
    $pemilik = userWithRole(UserRole::Admin);
    kirimNotifikasi($pemilik);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->get(route('notifikasi.baca', $pemilik->notifications()->value('id')))
        ->assertNotFound();
});
