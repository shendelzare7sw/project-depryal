<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Aset;
use App\Services\Backup\BackupSistem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

test('perintah backup membuat ZIP berisi dump database, foto aset, dan laporan', function () {
    $aset = Aset::factory()->create(['nama_barang' => "Gedung O'Brien"]);
    UploadedFile::fake()->image('a.jpg')->storeAs("aset/{$aset->id}", 'foto.jpg', 'public');
    Storage::disk('local')->put('laporan/uji.pdf', '%PDF-1.7');

    $this->artisan('sikaset:backup')->assertSuccessful();

    $cadangan = app(BackupSistem::class)->daftar();
    expect($cadangan)->toHaveCount(1);

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path('backup/'.$cadangan[0]['nama']));
    $sql = $zip->getFromName('database.sql');
    expect($sql)->toContain('CREATE TABLE')->toContain("Gedung O''Brien")
        ->and($zip->locateName("storage-public/aset/{$aset->id}/foto.jpg"))->not->toBeFalse()
        ->and($zip->locateName('storage-laporan/uji.pdf'))->not->toBeFalse()
        ->and($zip->locateName('BACA-SAYA.txt'))->not->toBeFalse();
    $zip->close();
});

test('dump database dapat dipulihkan ke database kosong', function () {
    Aset::factory()->count(2)->create();
    $nama = app(BackupSistem::class)->buat();
    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path('backup/'.$nama));
    $sql = (string) $zip->getFromName('database.sql');
    $zip->close();

    $pdo = new PDO('sqlite::memory:');
    $pdo->exec($sql);
    expect((int) $pdo->query('SELECT COUNT(*) FROM aset')->fetchColumn())->toBe(2);
});

test('pembersihan menghapus cadangan lama tetapi menyisakan 3 terbaru', function () {
    $disk = Storage::disk('local');
    foreach (range(1, 5) as $i) {
        $nama = sprintf('backup/sikaset-202601%02d-010000.zip', $i);
        $disk->put($nama, 'zip');
        touch($disk->path($nama), now()->subDays(40 - $i)->getTimestamp());
    }

    expect(app(BackupSistem::class)->bersihkan(14))->toBe(2)
        ->and(app(BackupSistem::class)->daftar())->toHaveCount(3);
});

test('admin membuat, mengunduh, dan menghapus cadangan; peran lain ditolak', function () {
    $admin = userWithRole(UserRole::Admin);

    $this->actingAs($admin)->post(route('backup.store'))->assertRedirect(route('backup.index'))->assertSessionHas('success');
    $nama = app(BackupSistem::class)->daftar()[0]['nama'];

    $this->get(route('backup.index'))->assertOk()->assertSee($nama);
    $this->get(route('backup.download', $nama))->assertOk()->assertDownload($nama);
    $this->get('/backup/..%2F.env')->assertNotFound();
    $this->delete(route('backup.destroy', $nama))->assertSessionHas('success');
    expect(app(BackupSistem::class)->daftar())->toBe([]);

    foreach ([UserRole::Operator, UserRole::Pimpinan] as $role) {
        $this->actingAs(userWithRole($role))->get(route('backup.index'))->assertForbidden();
    }
});
