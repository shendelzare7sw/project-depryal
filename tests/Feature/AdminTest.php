<?php

declare(strict_types=1);

use App\Enums\TindakanAset;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\KategoriAset;
use App\Models\User;
use App\Support\Setting;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

function dataPengguna(array $override = []): array
{
    return array_merge([
        'name' => 'Staf Aset Baru', 'username' => 'staf_aset', 'email' => 'staf@batuceper.go.id', 'role' => 'operator',
        'is_active' => '1', 'nip' => '199001012020121001', 'jabatan' => 'Pengurus Barang', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
    ], $override);
}

function dataPengaturan(array $override = []): array
{
    return array_merge([
        'ambang_pertahankan' => '70', 'ambang_perbaiki' => '40', 'nama_instansi' => 'Kecamatan Batuceper',
        'alamat_instansi' => 'Jl. Raya Batuceper No. 1', 'nama_penandatangan' => 'Camat Uji', 'nip_penandatangan' => '1970', 'jabatan_penandatangan' => 'Camat Batuceper',
    ], $override);
}

test('admin membuat pengguna baru yang bisa login', function () {
    $this->actingAs(userWithRole(UserRole::Admin))->post(route('pengguna.store'), dataPengguna())
        ->assertRedirect(route('pengguna.index'))->assertSessionHas('success');

    $user = User::firstWhere('username', 'staf_aset');
    expect($user->role)->toBe(UserRole::Operator)
        ->and($user->is_active)->toBeTrue()
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();

    auth()->logout();
    $this->post(route('login'), ['login' => 'staf_aset', 'password' => 'rahasia123'])->assertRedirect(route('dashboard'));
});

test('validasi pengguna: username unik, peran valid, password dikonfirmasi', function () {
    User::factory()->create(['username' => 'staf_aset']);

    $this->actingAs(userWithRole(UserRole::Admin))
        ->post(route('pengguna.store'), dataPengguna(['role' => 'superuser', 'password_confirmation' => 'beda12345']))
        ->assertSessionHasErrors(['username', 'role', 'password']);
});

test('admin mengubah peran pengguna lain dan memfilter daftar', function () {
    $admin = userWithRole(UserRole::Admin);
    $target = userWithRole(UserRole::Operator);

    $this->actingAs($admin)->put(route('pengguna.update', $target), dataPengguna(['username' => $target->username, 'email' => null, 'role' => 'pimpinan', 'password' => null, 'password_confirmation' => null]))
        ->assertSessionHasNoErrors();
    expect($target->refresh()->role)->toBe(UserRole::Pimpinan);

    $this->actingAs($admin)->get(route('pengguna.index', ['role' => 'pimpinan']))
        ->assertOk()->assertViewHas('users', fn ($p) => $p->every(fn ($u) => $u->role === UserRole::Pimpinan));
});

test('admin tidak bisa menurunkan peran, menonaktifkan, atau mereset password dirinya sendiri', function () {
    $admin = userWithRole(UserRole::Admin);

    $this->actingAs($admin)->put(route('pengguna.update', $admin), dataPengguna(['username' => $admin->username, 'email' => null, 'role' => 'operator', 'is_active' => '0', 'password' => null, 'password_confirmation' => null]))
        ->assertSessionHasErrors(['role', 'is_active']);
    $this->actingAs($admin)->post(route('pengguna.status', $admin))->assertSessionHas('error');
    $this->actingAs($admin)->post(route('pengguna.reset-password', $admin))->assertSessionHas('error');

    $admin->refresh();
    expect($admin->role)->toBe(UserRole::Admin)->and($admin->is_active)->toBeTrue();
});

test('admin menonaktifkan pengguna lain sehingga tidak bisa login, lalu mengaktifkannya kembali', function () {
    $admin = userWithRole(UserRole::Admin);
    $target = User::factory()->create(['role' => UserRole::Operator, 'username' => 'nonaktif_uji', 'password' => 'password']);

    $this->actingAs($admin)->post(route('pengguna.status', $target))->assertSessionHas('success');
    expect($target->refresh()->is_active)->toBeFalse();

    auth()->logout();
    $this->post(route('login'), ['login' => 'nonaktif_uji', 'password' => 'password']);
    $this->assertGuest();

    $this->actingAs($admin)->post(route('pengguna.status', $target));
    expect($target->refresh()->is_active)->toBeTrue();
});

test('reset password menampilkan password baru sekali dan tidak mencatat password di audit', function () {
    $admin = userWithRole(UserRole::Admin);
    $target = userWithRole(UserRole::Pimpinan);

    $response = $this->actingAs($admin)->post(route('pengguna.reset-password', $target))->assertSessionHas('password_baru');
    $baru = session('password_baru')['password'];

    expect(Hash::check($baru, $target->refresh()->password))->toBeTrue()
        ->and(Activity::where('log_name', 'pengguna')->get()->contains(fn ($a) => str_contains(json_encode($a->properties), 'password')))->toBeFalse();

    $this->actingAs($admin)->get(route('pengguna.index'))->assertSee($baru);
    $this->actingAs($admin)->get(route('pengguna.index'))->assertDontSee($baru);
});

test('admin menyimpan pengaturan; ambang baru dipakai perhitungan berikutnya dan tercatat di audit', function () {
    $admin = userWithRole(UserRole::Admin);

    $this->actingAs($admin)->put(route('pengaturan.update'), dataPengaturan(['ambang_pertahankan' => '60', 'ambang_perbaiki' => '20']))
        ->assertRedirect(route('pengaturan.index'))->assertSessionHas('success');

    expect(Setting::ambangPertahankan())->toBe(60.0)->and(Setting::namaPenandatangan())->toBe('Camat Uji');

    $log = Activity::where('log_name', 'pengaturan')->sole();
    expect($log->causer_id)->toBe($admin->id)
        ->and($log->properties['attributes']['ambang_pertahankan'])->toBe('60');

    ['aset' => $aset, 'periode' => $periode] = periodeGolden();
    expect(HasilMoora::where(['periode_id' => $periode->id, 'aset_id' => $aset['A1']->id])->value('rekomendasi'))->toBe(TindakanAset::Pertahankan)
        ->and($periode->snapshot_ambang)->toEqual(['pertahankan' => 60, 'perbaiki' => 20]);
});

test('ambang pertahankan harus lebih besar dari ambang perbaiki dan dalam 0–100', function () {
    $this->actingAs(userWithRole(UserRole::Admin))->put(route('pengaturan.update'), dataPengaturan(['ambang_pertahankan' => '30', 'ambang_perbaiki' => '40']))
        ->assertSessionHasErrors('ambang_pertahankan');
    $this->actingAs(userWithRole(UserRole::Admin))->put(route('pengaturan.update'), dataPengaturan(['ambang_pertahankan' => '120']))
        ->assertSessionHasErrors('ambang_pertahankan');
});

test('perubahan data tercatat di audit log dengan pelaku, modul, dan nilai sebelum/sesudah', function () {
    $operator = userWithRole(UserRole::Operator);
    $kategori = KategoriAset::factory()->create();
    $this->actingAs($operator);
    $aset = Aset::factory()->create(['kategori_aset_id' => $kategori->id, 'nama_barang' => 'Gedung Audit']);
    $aset->update(['nama_barang' => 'Gedung Audit Baru']);

    $log = Activity::where('log_name', 'aset')->latest('id')->first();
    expect($log->description)->toBe('Aset diubah: Gedung Audit Baru ('.$aset->kode_barang.' / '.$aset->nup.')')
        ->and($log->causer_id)->toBe($operator->id)
        ->and($log->properties['old']['nama_barang'])->toBe('Gedung Audit')
        ->and($log->properties['attributes']['nama_barang'])->toBe('Gedung Audit Baru');

    $this->actingAs(userWithRole(UserRole::Admin))->get(route('audit-log.index', ['modul' => 'aset']))
        ->assertOk()->assertSee('Aset diubah: Gedung Audit Baru')->assertDontSee('Kategori Aset ditambahkan');
});

test('profil: ubah nama, ganti password wajib verifikasi password lama', function () {
    $user = User::factory()->create(['role' => UserRole::Pimpinan, 'password' => 'lama12345']);

    $this->actingAs($user)->put(route('profil.update'), ['name' => 'Nama Baru', 'email' => '', 'current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
        ->assertSessionHasErrors('current_password');

    $this->actingAs($user)->put(route('profil.update'), ['name' => 'Nama Baru', 'email' => '', 'current_password' => 'lama12345', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
        ->assertSessionHasNoErrors()->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Nama Baru')->and($user->email)->toBeNull()->and(Hash::check('baru12345', $user->password))->toBeTrue();
});

test('modul admin tertutup bagi operator dan pimpinan', function (UserRole $role) {
    $user = userWithRole($role);
    $lain = userWithRole(UserRole::Operator);

    $this->actingAs($user)->get(route('pengguna.create'))->assertForbidden();
    $this->actingAs($user)->post(route('pengguna.store'), dataPengguna())->assertForbidden();
    $this->actingAs($user)->post(route('pengguna.status', $lain))->assertForbidden();
    $this->actingAs($user)->post(route('pengguna.reset-password', $lain))->assertForbidden();
    $this->actingAs($user)->get(route('pengaturan.index'))->assertForbidden();
    $this->actingAs($user)->put(route('pengaturan.update'), dataPengaturan())->assertForbidden();
    $this->actingAs($user)->get(route('audit-log.index'))->assertForbidden();
})->with([UserRole::Operator, UserRole::Pimpinan]);
