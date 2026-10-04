<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Aset;
use App\Support\Tampil;

test('daftar aset memakai "Tampilkan lebih banyak" bukan pagination bernomor', function () {
    Aset::factory()->count(20)->create();
    $operator = userWithRole(UserRole::Operator);

    $response = $this->actingAs($operator)->get(route('aset.index'))->assertOk()
        ->assertSee('Menampilkan <strong class="text-zinc-800">15</strong> dari <strong class="text-zinc-800">20</strong>', false)
        ->assertSee('Tampilkan 5 lagi')->assertDontSee('pagination.')->assertSee('id="m-15"', false)->assertSee('id="d-15"', false);
    expect($response->viewData('aset')->count())->toBe(15);

    $this->get(route('aset.index', ['tampil' => 30]))->assertOk()
        ->assertSee('id="m-20"', false)->assertDontSee('Tampilkan 5 lagi')->assertSee('Semua data sudah ditampilkan');
});

test('parameter tampil dibulatkan ke kelipatan langkah, dibatasi, dan filter tetap terbawa', function () {
    Aset::factory()->count(17)->create(['nama_barang' => 'Posyandu Uji']);
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->get(route('aset.index', ['q' => 'Posyandu']))
        ->assertSee(e(route('aset.index', ['q' => 'Posyandu', 'tampil' => 30])), false);

    foreach (['-5' => 15, '16' => 30, '99999' => Tampil::MAKS, 'abc' => 15] as $minta => $hasil) {
        request()->query->set('tampil', $minta);
        expect(Tampil::jumlah())->toBe($hasil);
    }
});
