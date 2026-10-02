<?php

declare(strict_types=1);

use App\Enums\TipeKriteria;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\Kriteria;
use App\Models\KriteriaSkala;

function dataKriteria(array $override = []): array
{
    return array_merge([
        'kode' => 'C4',
        'nama' => 'Keamanan Bangunan',
        'tipe' => TipeKriteria::Benefit->value,
        'bobot_persen' => 25.5,
        'skala_min' => 1,
        'skala_maks' => 5,
        'urutan' => 4,
        'is_active' => '1',
        'keterangan' => 'Kondisi keamanan struktur.',
        'skala' => collect(range(1, 5))->mapWithKeys(fn ($n) => [$n => ['label' => "Label {$n}", 'deskripsi' => "Deskripsi {$n}"]])->all(),
    ], $override);
}

test('index menampilkan meteran total bobot: hijau bila 100%, merah bila tidak', function () {
    $operator = userWithRole(UserRole::Operator);
    Kriteria::factory()->create(['kode' => 'C1', 'bobot' => 0.6]);
    $c2 = Kriteria::factory()->create(['kode' => 'C2', 'bobot' => 0.3]);
    Kriteria::factory()->create(['kode' => 'C9', 'bobot' => 0.5, 'is_active' => false]);

    $this->actingAs($operator)->get(route('kriteria.index'))
        ->assertOk()->assertViewHas('totalBobot', 90.0)->assertSee('progress-error')->assertSee('harus tepat 100%');

    $c2->update(['bobot' => 0.4]);

    $this->actingAs($operator)->get(route('kriteria.index'))
        ->assertViewHas('totalBobot', 100.0)->assertSee('progress-success')->assertSee('sudah 100%');
});

test('operator menambah kriteria: bobot persen disimpan desimal, rubrik skala dibuat', function () {
    $this->actingAs(userWithRole(UserRole::Operator))->post(route('kriteria.store'), dataKriteria())
        ->assertRedirect(route('kriteria.index'))->assertSessionHas('success');

    $kriteria = Kriteria::firstWhere('kode', 'C4');
    expect($kriteria->bobot)->toBe(0.255)
        ->and($kriteria->is_active)->toBeTrue()
        ->and($kriteria->skala()->pluck('label', 'nilai')->all())->toBe([1 => 'Label 1', 2 => 'Label 2', 3 => 'Label 3', 4 => 'Label 4', 5 => 'Label 5']);
});

test('label rubrik wajib diisi untuk nilai di dalam rentang skala', function () {
    $data = dataKriteria();
    $data['skala'][3]['label'] = '';

    $this->actingAs(userWithRole(UserRole::Operator))->post(route('kriteria.store'), $data)
        ->assertSessionHasErrors(['skala.3.label']);
});

test('validasi kriteria: bobot 0–100, skala maks > min, kode unik', function () {
    Kriteria::factory()->create(['kode' => 'C4']);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('kriteria.store'), dataKriteria(['bobot_persen' => 120, 'skala_min' => 3, 'skala_maks' => 2]))
        ->assertSessionHasErrors(['kode', 'bobot_persen', 'skala_maks']);
});

test('rentang skala diperkecil menghapus rubrik di luar rentang dan kriteria bisa dinonaktifkan', function () {
    $operator = userWithRole(UserRole::Operator);
    $this->actingAs($operator)->post(route('kriteria.store'), dataKriteria());
    $kriteria = Kriteria::firstWhere('kode', 'C4');

    $this->actingAs($operator)->put(route('kriteria.update', $kriteria), dataKriteria(['skala_maks' => 3, 'is_active' => '0']))
        ->assertSessionHasNoErrors();

    $kriteria->refresh();
    expect($kriteria->is_active)->toBeFalse()
        ->and($kriteria->skala()->pluck('nilai')->all())->toBe([1, 2, 3]);
});

test('kriteria yang dipakai periode final: tipe & skala terkunci, hapus disembunyikan dan ditolak', function () {
    $operator = userWithRole(UserRole::Operator);
    $kriteria = Kriteria::factory()->create(['kode' => 'C1', 'tipe' => TipeKriteria::Cost, 'bobot' => 0.4]);
    periodeFinalDengan(Aset::factory()->create(), $kriteria);

    $this->actingAs($operator)->get(route('kriteria.index'))
        ->assertSee('Terkunci')->assertDontSee('Hapus kriteria?');
    $this->actingAs($operator)->get(route('kriteria.edit', $kriteria))
        ->assertOk()->assertSee('dikunci');

    $this->actingAs($operator)->put(route('kriteria.update', $kriteria), dataKriteria([
        'kode' => 'C1', 'tipe' => TipeKriteria::Benefit->value, 'skala_min' => 2, 'skala_maks' => 4, 'bobot_persen' => 35,
    ]))->assertSessionHasNoErrors();

    $kriteria->refresh();
    expect($kriteria->tipe)->toBe(TipeKriteria::Cost)
        ->and($kriteria->skala_min)->toBe(1)
        ->and($kriteria->skala_maks)->toBe(5)
        ->and($kriteria->bobot)->toBe(0.35);

    $this->actingAs($operator)->delete(route('kriteria.destroy', $kriteria))->assertSessionHas('error');
    expect(Kriteria::find($kriteria->id))->not->toBeNull();
});

test('kriteria tanpa nilai dapat dihapus beserta rubriknya', function () {
    $operator = userWithRole(UserRole::Operator);
    $this->actingAs($operator)->post(route('kriteria.store'), dataKriteria());
    $kriteria = Kriteria::firstWhere('kode', 'C4');

    $this->actingAs($operator)->delete(route('kriteria.destroy', $kriteria))
        ->assertRedirect(route('kriteria.index'))->assertSessionHas('success');

    expect(Kriteria::find($kriteria->id))->toBeNull()
        ->and(KriteriaSkala::where('kriteria_id', $kriteria->id)->count())->toBe(0);
});

test('admin dan pimpinan hanya bisa membaca kriteria', function (UserRole $role) {
    $user = userWithRole($role);
    $kriteria = Kriteria::factory()->create();

    $this->actingAs($user)->get(route('kriteria.index'))->assertOk()->assertSee($kriteria->nama)->assertDontSee(route('kriteria.create'));
    $this->actingAs($user)->get(route('kriteria.create'))->assertForbidden();
    $this->actingAs($user)->post(route('kriteria.store'), dataKriteria())->assertForbidden();
    $this->actingAs($user)->get(route('kriteria.edit', $kriteria))->assertForbidden();
    $this->actingAs($user)->put(route('kriteria.update', $kriteria), dataKriteria())->assertForbidden();
    $this->actingAs($user)->delete(route('kriteria.destroy', $kriteria))->assertForbidden();
})->with([UserRole::Admin, UserRole::Pimpinan]);
