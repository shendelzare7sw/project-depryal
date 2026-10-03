<?php

declare(strict_types=1);

use App\Enums\StatusAset;
use App\Enums\StatusPeriode;
use App\Enums\UserRole;
use App\Exports\AsetTemplateExport;
use App\Models\Aset;
use App\Models\HasilMoora;
use App\Models\Laporan;
use App\Models\PeriodePenilaian;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

/**
 * Alur bisnis utama lintas role (01-PRODUCT-SPEC §5): Operator → Pimpinan → Admin.
 */
test('alur lengkap: import → periode → nilai → hitung → keputusan → finalisasi → laporan → audit → buka kembali', function () {
    Storage::fake('local');
    Storage::fake('public');
    $admin = userWithRole(UserRole::Admin);
    $operator = userWithRole(UserRole::Operator);
    $pimpinan = userWithRole(UserRole::Pimpinan);
    $kriteria = siapkanKriteria();

    // 1. Operator mengimpor data BMD (4 aset) → admin mendapat notifikasi.
    $baris = collect(range(1, 4))->map(fn ($i) => ['Gedung Kantor', '1.03.01.01.00'.$i, 1, "Gedung Uji {$i}", 1, '100', '01/01/2010', '100.000.000', '100.000.000', 30, '0', 10 + $i, '100.000.000', 'Batuceper'])->all();
    $this->actingAs($operator)->post(route('aset.import.store'), ['file' => UploadedFile::fake()->createWithContent('bmd.xlsx', Excel::raw(new AsetTemplateExport($baris), ExcelWriter::XLSX))]);
    $this->actingAs($operator)->post(route('aset.import.confirm'))->assertSessionHas('success', '4 data aset berhasil diimpor.');
    expect($admin->notifications()->first()->data['judul'])->toBe('Import data BMD selesai');

    // 2. Operator membuat periode untuk semua aset aktif.
    $this->actingAs($operator)->post(route('periode.store'), ['nama' => 'Periode Uji', 'tanggal_mulai' => '2026-10-01', 'cakupan' => 'semua']);
    $periode = PeriodePenilaian::sole();
    $this->actingAs($operator)->get(route('periode.show', $periode))->assertSee('Hitung MOORA belum bisa dijalankan');

    // 3. Operator mengisi nilai tiap aset dengan "Simpan & Lanjut"; status → dinilai.
    $nilai = [[5, 4, 1], [4, 3, 2], [3, 5, 3], [2, 2, 5]];
    foreach (Aset::orderBy('kode_barang')->get() as $i => $aset) {
        $this->actingAs($operator)->put(route('penilaian.update', [$periode, $aset]), [
            'kriteria' => [$kriteria[0]->id => $nilai[$i][0], $kriteria[1]->id => $nilai[$i][1], $kriteria[2]->id => $nilai[$i][2]],
            'deskripsi_kondisi' => 'Kondisi diperiksa.', 'lanjut' => 1,
        ])->assertSessionHasNoErrors();
    }
    expect($periode->refresh()->status)->toBe(StatusPeriode::Dinilai);

    // 4. Operator menjalankan Hitung MOORA → pimpinan mendapat notifikasi.
    $this->actingAs($operator)->post(route('periode.hitung', $periode))->assertRedirect(route('peringkat.index', $periode));
    $notif = $pimpinan->notifications()->first();
    expect($notif->data['judul'])->toBe('Peringkat MOORA siap ditinjau');

    // 5. Pimpinan membuka notifikasi → peringkat; menu Keputusan berisi 4 aset menunggu.
    $this->actingAs($pimpinan)->get(route('notifikasi.baca', $notif->id))->assertRedirect(route('peringkat.index', $periode));
    $this->actingAs($pimpinan)->get(route('keputusan.index'))->assertSee('4 aset menunggu keputusan Anda');

    // 6. Pimpinan memutuskan semua aset (satu berbeda dari rekomendasi dengan catatan).
    foreach (HasilMoora::where('periode_id', $periode->id)->orderBy('ranking')->get() as $i => $h) {
        $beda = $i === 0;
        $this->actingAs($pimpinan)->put(route('keputusan.update', [$periode, $h->aset_id]), [
            'tindakan' => $beda ? 'perbaiki' : $h->rekomendasi->value,
            'catatan' => $beda ? 'Atap perlu diganti sebelum dipertahankan.' : null,
        ])->assertSessionHasNoErrors();
    }

    // 7. Pimpinan memfinalisasi → status aset berubah, operator diberi tahu.
    $this->actingAs($pimpinan)->post(route('periode.finalisasi', $periode))->assertSessionHas('success');
    expect($periode->refresh()->status)->toBe(StatusPeriode::Final)
        ->and(Aset::where('status', StatusAset::DalamPerbaikan->value)->count())->toBeGreaterThanOrEqual(1)
        ->and($operator->notifications()->first()->data['judul'])->toBe('Periode difinalisasi');

    // 8. Operator membuat laporan PDF lengkap; admin mengunduhnya.
    $this->actingAs($operator)->post(route('laporan.store'), ['periode_id' => $periode->id, 'jenis' => 'lengkap', 'format' => 'pdf'])->assertSessionHas('success');
    $this->actingAs($admin)->get(route('laporan.download', Laporan::sole()))->assertOk();

    // 9. Admin melihat jejak audit (aset, nilai, keputusan, periode).
    $modul = Activity::pluck('log_name')->unique()->sort()->values()->all();
    expect($modul)->toContain('aset', 'nilai', 'keputusan', 'periode');
    $this->actingAs($admin)->get(route('audit-log.index', ['modul' => 'keputusan']))->assertOk()->assertSee('Keputusan ditambahkan');

    // 10. Operator membuka kembali periode final dengan alasan → pimpinan diberi tahu.
    $this->actingAs($operator)->post(route('periode.buka-kembali', $periode), ['alasan' => 'Koreksi nilai Gedung Uji 1.'])->assertSessionHas('success');
    expect($periode->refresh()->status)->toBe(StatusPeriode::Dihitung)
        ->and($pimpinan->notifications()->first()->data['judul'])->toBe('Periode dibuka kembali');
});
