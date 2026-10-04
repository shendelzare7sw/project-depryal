<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Laporan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

test('laporan PDF memuat kode verifikasi dan sidik jari berkas', function () {
    ['periode' => $periode] = periodeGolden();

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'lengkap', 'format' => 'pdf'])->assertSessionHas('success');

    $laporan = Laporan::sole();
    $isi = Storage::disk('local')->get($laporan->path);
    expect($laporan->kode_verifikasi)->toMatch('/^[0-9A-F]{16}$/')
        ->and($laporan->sha256)->toBe(hash('sha256', $isi));
});

test('halaman verifikasi publik menampilkan dokumen terdaftar tanpa login', function () {
    ['periode' => $periode] = periodeGolden();
    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'peringkat', 'format' => 'xlsx']);
    $laporan = Laporan::sole();
    auth()->logout();

    $this->get(route('verifikasi.show', strtolower($laporan->kode_verifikasi)))
        ->assertOk()->assertSee('Dokumen terdaftar')->assertSee($periode->nama)->assertDontSee($laporan->path);
    $this->get(route('verifikasi.show', 'TIDAKADA123'))->assertNotFound()->assertSee('Dokumen tidak ditemukan');
});

test('unggah berkas membandingkan isi dengan arsip', function () {
    ['periode' => $periode] = periodeGolden();
    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'peringkat', 'format' => 'pdf']);
    $laporan = Laporan::sole();
    $asli = UploadedFile::fake()->createWithContent('asli.pdf', Storage::disk('local')->get($laporan->path));
    $palsu = UploadedFile::fake()->createWithContent('palsu.pdf', '%PDF-1.7 diubah');
    $url = route('verifikasi.show', $laporan->kode_verifikasi);

    $this->from($url)->post(route('verifikasi.cek', $laporan->kode_verifikasi), ['berkas' => $asli])->assertSessionHas('hasil_cek', true);
    $this->from($url)->post(route('verifikasi.cek', $laporan->kode_verifikasi), ['berkas' => $palsu])->assertSessionHas('hasil_cek', false);
});
