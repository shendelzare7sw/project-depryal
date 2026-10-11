<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Support\Navigation;

test('setiap menu di navigasi setiap peran menampilkan panduan singkat', function (UserRole $role) {
    ['periode' => $periode] = periodeGolden();
    $user = userWithRole($role);

    foreach (array_merge(...array_column(Navigation::for($user), 'items')) as $item) {
        $response = $this->actingAs($user)->followingRedirects()->get(route($item['route']));
        $response->assertOk()->assertSee('Panduan singkat', false);
    }

    expect($periode)->not->toBeNull();
})->with([UserRole::Admin, UserRole::Operator, UserRole::Pimpinan]);

test('halaman Peringkat Aset menjelaskan peringkat, skor kelayakan, dan rekomendasi tanpa istilah teknis', function () {
    ['periode' => $periode] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Pimpinan))->get(route('peringkat.index', $periode))
        ->assertSee('Peringkat Aset')->assertSee('hasil membandingkan semua aset')
        ->assertSee('Skor kelayakan (0–100)')->assertSee('Mulai Putuskan')->assertDontSee('Hasil MOORA');
});
