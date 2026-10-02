<?php

declare(strict_types=1);

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\KategoriAset;
use App\Models\Keputusan;
use App\Models\NilaiKriteriaAset;
use App\Models\PeriodePenilaian;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('operator membuat periode untuk semua aset aktif', function () {
    Aset::factory()->count(3)->create();
    Aset::factory()->create(['status' => StatusAset::DiusulkanHapus]);

    $this->actingAs($operator = userWithRole(UserRole::Operator))->post(route('periode.store'), [
        'nama' => 'Penilaian 2026', 'tanggal_mulai' => '2026-10-01', 'cakupan' => 'semua',
    ])->assertSessionHasNoErrors();

    $periode = PeriodePenilaian::sole();
    expect($periode->status)->toBe(StatusPeriode::Draft)
        ->and($periode->aset()->count())->toBe(3)
        ->and($periode->created_by)->toBe($operator->id);
});

test('cakupan per kategori, perlu perhatian, dan pilih manual', function () {
    $kat = KategoriAset::factory()->create();
    Aset::factory()->count(2)->create(['kategori_aset_id' => $kat->id, 'sisa_ueb' => 20]);
    $tua = Aset::factory()->count(2)->create(['sisa_ueb' => 2]);
    $operator = userWithRole(UserRole::Operator);
    $buat = fn (array $data) => $this->actingAs($operator)->post(route('periode.store'), ['nama' => 'P', 'tanggal_mulai' => '2026-10-01'] + $data);

    $buat(['cakupan' => 'kategori', 'kategori_ids' => [$kat->id]]);
    expect(PeriodePenilaian::latest('id')->first()->aset()->pluck('kategori_aset_id')->unique()->all())->toBe([$kat->id]);
    PeriodePenilaian::query()->update(['status' => StatusPeriode::Final]);

    $buat(['cakupan' => 'perhatian']);
    expect(PeriodePenilaian::latest('id')->first()->aset()->pluck('aset.id')->sort()->values()->all())->toBe($tua->pluck('id')->sort()->values()->all());
    PeriodePenilaian::query()->update(['status' => StatusPeriode::Final]);

    $buat(['cakupan' => 'pilih', 'aset_ids' => [$tua[0]->id]])->assertSessionHas('error');
});

test('hanya satu periode aktif yang diizinkan', function () {
    Aset::factory()->count(2)->create();
    periodeDraft();

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('periode.store'), ['nama' => 'Baru', 'tanggal_mulai' => '2026-10-01', 'cakupan' => 'semua'])
        ->assertSessionHas('error');

    expect(PeriodePenilaian::count())->toBe(1);
});

test('halaman periode menampilkan progres dan alasan Hitung MOORA dinonaktifkan', function () {
    siapkanKriteria();
    $periode = periodeDraft();

    $this->actingAs(userWithRole(UserRole::Operator))->get(route('periode.show', $periode))
        ->assertOk()->assertSee('Hitung MOORA belum bisa dijalankan')->assertSee('9 nilai belum diisi')
        ->assertSee(route('penilaian.edit', [$periode, $periode->aset->first()]));
});

test('input nilai per aset: sebagian → draft, lengkap → dinilai, foto & kondisi tersimpan', function () {
    Storage::fake('public');
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(2);
    [$a1, $a2] = $periode->aset->sortBy(['kode_barang', 'nup'])->values()->all();
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->put(route('penilaian.update', [$periode, $a1]), [
        'kriteria' => [$kriteria[0]->id => 4, $kriteria[1]->id => 3, $kriteria[2]->id => ''],
        'deskripsi_kondisi' => 'Atap bocor di ruang tengah.',
        'fotos' => [UploadedFile::fake()->image('kondisi.jpg')],
        'lanjut' => 1,
    ])->assertRedirect(route('penilaian.edit', [$periode, $a2]));

    expect($periode->refresh()->status)->toBe(StatusPeriode::Draft)
        ->and(NilaiKriteriaAset::where('aset_id', $a1->id)->count())->toBe(2)
        ->and($a1->fotos()->where('periode_id', $periode->id)->count())->toBe(1)
        ->and($periode->aset()->whereKey($a1->id)->first()->pivot->deskripsi_kondisi)->toBe('Atap bocor di ruang tengah.');

    foreach ([$a1, $a2] as $aset) {
        $this->actingAs($operator)->put(route('penilaian.update', [$periode, $aset]), [
            'kriteria' => [$kriteria[0]->id => 4, $kriteria[1]->id => 3, $kriteria[2]->id => 2],
        ]);
    }

    expect($periode->refresh()->status)->toBe(StatusPeriode::Dinilai);

    $this->actingAs($operator)->put(route('penilaian.update', [$periode, $a2]), [
        'kriteria' => [$kriteria[0]->id => 4, $kriteria[1]->id => 3, $kriteria[2]->id => ''],
    ]);
    expect($periode->refresh()->status)->toBe(StatusPeriode::Draft);
});

test('nilai di luar rentang skala ditolak dan halaman input menampilkan rubrik', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(2);
    $aset = $periode->aset->first();
    $operator = userWithRole(UserRole::Operator);

    $this->actingAs($operator)->get(route('penilaian.edit', [$periode, $aset]))
        ->assertOk()->assertSee('Skala 5')->assertSee('capture="environment"', false);

    $this->actingAs($operator)->put(route('penilaian.update', [$periode, $aset]), ['kriteria' => [$kriteria[0]->id => 7]])
        ->assertSessionHasErrors("kriteria.{$kriteria[0]->id}");
});

test('aset di luar periode tidak bisa dinilai', function () {
    siapkanKriteria();
    $periode = periodeDraft(2);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->get(route('penilaian.edit', [$periode, Aset::factory()->create()]))->assertNotFound();
});

test('hitung MOORA dari halaman periode → dihitung, hasil tersimpan, pimpinan diberi tahu', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(3);
    isiSemuaNilai($periode, $kriteria);
    $pimpinan = userWithRole(UserRole::Pimpinan);

    $this->actingAs(userWithRole(UserRole::Operator))->post(route('periode.hitung', $periode))
        ->assertRedirect(route('peringkat.index', $periode))->assertSessionHas('success');

    expect($periode->refresh()->status)->toBe(StatusPeriode::Dihitung)
        ->and(HasilMoora::where('periode_id', $periode->id)->count())->toBe(3)
        ->and($pimpinan->notifications()->first()->data['judul'])->toBe('Peringkat MOORA siap ditinjau');
});

test('mengubah nilai setelah dihitung menghapus hasil & keputusan dan memberi peringatan', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(2);
    isiSemuaNilai($periode, $kriteria);
    $operator = userWithRole(UserRole::Operator);
    $this->actingAs($operator)->post(route('periode.hitung', $periode));
    Keputusan::factory()->create(['periode_id' => $periode->id, 'aset_id' => $periode->aset->first()->id]);
    $aset = $periode->aset->first();

    $this->actingAs($operator)->get(route('penilaian.edit', [$periode, $aset]))->assertSee('menghapus hasil MOORA');

    $this->actingAs($operator)->put(route('penilaian.update', [$periode, $aset]), [
        'kriteria' => [$kriteria[0]->id => 5, $kriteria[1]->id => 5, $kriteria[2]->id => 1],
    ]);

    expect($periode->refresh()->status)->toBe(StatusPeriode::Dinilai)
        ->and(HasilMoora::where('periode_id', $periode->id)->count())->toBe(0)
        ->and(Keputusan::where('periode_id', $periode->id)->count())->toBe(0);
});

test('periode final terkunci dan dapat dibuka kembali dengan alasan', function () {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(2);
    isiSemuaNilai($periode, $kriteria);
    $periode->update(['status' => StatusPeriode::Final, 'difinalisasi_pada' => now()]);
    $operator = userWithRole(UserRole::Operator);
    $pimpinan = userWithRole(UserRole::Pimpinan);

    $this->actingAs($operator)->put(route('penilaian.update', [$periode, $periode->aset->first()]), ['kriteria' => [$kriteria[0]->id => 1]])
        ->assertSessionHas('error');
    $this->actingAs($operator)->post(route('periode.buka-kembali', $periode), ['alasan' => 'pendek'])->assertSessionHasErrors('alasan');

    $this->actingAs($operator)->post(route('periode.buka-kembali', $periode), ['alasan' => 'Koreksi nilai biaya pemeliharaan.'])
        ->assertRedirect(route('periode.show', $periode));

    expect($periode->refresh()->status)->toBe(StatusPeriode::Dihitung)
        ->and($periode->alasan_buka_kembali)->toBe('Koreksi nilai biaya pemeliharaan.')
        ->and($pimpinan->notifications()->count())->toBe(1);
});

test('buka kembali ditolak bila ada periode lain yang aktif', function () {
    $final = PeriodePenilaian::factory()->create(['status' => StatusPeriode::Final]);
    periodeDraft(2);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->post(route('periode.buka-kembali', $final), ['alasan' => 'Koreksi data aset tertentu.'])->assertSessionHas('error');

    expect($final->refresh()->status)->toBe(StatusPeriode::Final);
});

test('operator mengubah identitas periode', function () {
    $periode = periodeDraft(2);

    $this->actingAs(userWithRole(UserRole::Operator))
        ->put(route('periode.update', $periode), ['nama' => 'Nama Baru', 'tanggal_mulai' => '2026-10-05'])
        ->assertRedirect(route('periode.show', $periode));

    expect($periode->refresh()->nama)->toBe('Nama Baru');
});

test('admin dan pimpinan hanya bisa melihat periode', function (UserRole $role) {
    $kriteria = siapkanKriteria();
    $periode = periodeDraft(2);
    isiSemuaNilai($periode, $kriteria);
    $user = userWithRole($role);

    $this->actingAs($user)->get(route('periode.index'))->assertOk()->assertDontSee(route('periode.create'));
    $this->actingAs($user)->get(route('periode.show', $periode))->assertOk()->assertDontSee(route('periode.hitung', $periode));
    $this->actingAs($user)->get(route('periode.create'))->assertForbidden();
    $this->actingAs($user)->post(route('periode.hitung', $periode))->assertForbidden();
    $this->actingAs($user)->get(route('penilaian.edit', [$periode, $periode->aset->first()]))->assertForbidden();
    $this->actingAs($user)->post(route('periode.buka-kembali', $periode), ['alasan' => 'Koreksi data aset tertentu.'])->assertForbidden();
})->with([UserRole::Admin, UserRole::Pimpinan]);
