{{--
    Template PDF (dompdf). PENGECUALIAN Aturan Emas: dompdf tidak menjalankan Tailwind CDN,
    sehingga gaya cetak ditulis sebagai <style> di file ini (lihat DECISIONS 2026-10-03).
--}}
@php
    $f4 = fn ($v) => number_format((float) $v, 4, ',', '.');
    $f2 = fn ($v) => number_format((float) $v, 2, ',', '.');
    $tampilPeringkat = $jenis !== \App\Enums\JenisLaporan::Keputusan;
    $tampilKeputusan = $jenis !== \App\Enums\JenisLaporan::Peringkat;
    $lengkap = $jenis === \App\Enums\JenisLaporan::Lengkap;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $jenis->label() }} — {{ $periode->nama }}</title>
<style>
    @page { margin: 22mm 16mm 20mm 16mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9pt; color: #1f2933; }
    .footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 7pt; color: #6b7280; border-top: 0.5pt solid #d1d5db; padding-top: 3pt; }
    .footer .halaman:after { content: "Halaman " counter(page); }
    .kop { width: 100%; border-bottom: 2.5pt double #1f5f59; padding-bottom: 6pt; margin-bottom: 10pt; }
    .kop td { vertical-align: middle; }
    .kop .instansi { font-size: 13pt; font-weight: bold; color: #0b2523; text-transform: uppercase; }
    .kop .alamat { font-size: 8pt; color: #4b5563; }
    h1 { font-size: 12pt; text-align: center; margin: 0 0 2pt; text-transform: uppercase; letter-spacing: 0.5pt; }
    .sub { text-align: center; font-size: 8.5pt; color: #4b5563; margin-bottom: 10pt; }
    h2 { font-size: 10pt; color: #1f5f59; margin: 12pt 0 5pt; border-left: 3pt solid #f59e0b; padding-left: 5pt; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #1f5f59; color: #fff; font-size: 7.5pt; text-transform: uppercase; padding: 4pt 5pt; text-align: left; }
    table.data td { border-bottom: 0.5pt solid #e5e7eb; padding: 3.5pt 5pt; vertical-align: top; }
    table.data tr:nth-child(even) td { background: #f6f8f8; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    .num, table.data th.num { text-align: right; white-space: nowrap; }
    table.data th.center { text-align: center; }
    .center { text-align: center; }
    .mono { font-family: "DejaVu Sans Mono", monospace; font-size: 7.5pt; white-space: nowrap; }
    .badge { padding: 1pt 4pt; border-radius: 3pt; font-size: 7.5pt; font-weight: bold; }
    .pertahankan { background: #d1fae5; color: #065f46; }
    .perbaiki { background: #fef3c7; color: #92400e; }
    .hapus { background: #ffe4e6; color: #9f1239; }
    .ringkas { width: 100%; border-collapse: separate; border-spacing: 4pt 0; margin: 0 -4pt 6pt; }
    .ringkas td { border: 0.5pt solid #d1d5db; border-radius: 4pt; padding: 5pt; text-align: center; }
    .ringkas .angka { font-size: 13pt; font-weight: bold; }
    .catatan { font-size: 7.5pt; color: #6b7280; margin-top: 8pt; }
    .ttd { width: 100%; margin-top: 18pt; page-break-inside: avoid; }
    .ttd td { vertical-align: top; }
    .verifikasi { font-size: 7pt; color: #4b5563; line-height: 1.4; }
    .verifikasi img { width: 62pt; height: 62pt; float: left; margin-right: 6pt; }
    .verifikasi .kode { font-family: DejaVu Sans Mono, monospace; font-weight: bold; color: #111827; font-size: 8pt; }
</style>
</head>
<body>
<div class="footer">
    <table width="100%"><tr>
        <td>SIKASET · Dicetak {{ now()->translatedFormat('d F Y H:i') }} oleh {{ $dicetakOleh->name }}</td>
        <td style="text-align: right"><span class="halaman"></span></td>
    </tr></table>
</div>

<table class="kop"><tr>
    @if (is_file($logo))<td style="width: 52pt"><img src="{{ $logo }}" style="width: 46pt; height: 46pt;" alt="Logo"></td>@endif
    <td>
        <div class="instansi">{{ $instansi['nama'] }}</div>
        <div class="alamat">{{ $instansi['alamat'] }}</div>
    </td>
</tr></table>

<h1>{{ $jenis->label() }}</h1>
<div class="sub">
    {{ $periode->nama }} · {{ $periode->tanggal_mulai?->translatedFormat('d F Y') }}{{ $periode->tanggal_selesai ? ' – '.$periode->tanggal_selesai->translatedFormat('d F Y') : '' }}
    · Status: {{ $periode->status->label() }} · Metode MOORA
</div>

<table class="ringkas"><tr>
    <td><div class="angka">{{ $total }}</div>Aset dinilai</td>
    @foreach (\App\Enums\TindakanAset::cases() as $t)
    <td><div class="angka">{{ $ringkasan[$t->value] }}</div>Rekomendasi {{ $t->label() }}</td>
    @endforeach
    <td><div class="angka">{{ $diputuskan }}</div>Sudah diputuskan</td>
</tr></table>

@if ($lengkap)
<h2>Kriteria &amp; Bobot (saat dihitung)</h2>
<table class="data">
    <thead><tr><th style="width: 40pt">Kode</th><th>Kriteria</th><th style="width: 60pt">Tipe</th><th class="num" style="width: 50pt">Bobot</th><th class="num" style="width: 70pt">Penyebut d<sub>j</sub></th></tr></thead>
    <tbody>
        @foreach ($kriteria as $k)
        <tr><td class="mono">{{ $k['kode'] }}</td><td>{{ $k['nama'] }}</td><td>{{ $k['tipe'] === 'cost' ? 'Cost' : 'Benefit' }}</td><td class="num">{{ $f2($k['bobot'] * 100) }}%</td><td class="num mono">{{ number_format($penyebut[$k['id']], 6, ',', '.') }}</td></tr>
        @endforeach
    </tbody>
</table>

<h2>Matriks Nilai Alternatif</h2>
<table class="data">
    <thead><tr><th class="center" style="width: 26pt">No</th><th>Nama Barang</th>@foreach ($kriteria as $k)<th class="num" style="width: 34pt">{{ $k['kode'] }}</th>@endforeach</tr></thead>
    <tbody>
        @foreach ($hasil as $h)
        <tr><td class="center">{{ $loop->iteration }}</td><td>{{ $h->aset?->nama_barang }}</td>@foreach ($kriteria as $k)<td class="num">{{ rtrim(rtrim(number_format((float) ($matriks[$h->aset_id][$k['id']] ?? 0), 2, ',', ''), '0'), ',') }}</td>@endforeach</tr>
        @endforeach
    </tbody>
</table>
@endif

@if ($tampilPeringkat)
<h2>Peringkat Kelayakan Aset</h2>
<table class="data">
    <thead><tr><th class="center" style="width: 30pt">Rank</th><th style="width: 112pt">Kode / NUP</th><th>Nama Barang</th><th class="num" style="width: 46pt">Y<sub>i</sub></th><th class="num" style="width: 40pt">Skor</th><th style="width: 62pt">Rekomendasi</th></tr></thead>
    <tbody>
        @foreach ($baris as $b)
        <tr>
            <td class="center"><strong>{{ $b['hasil']->ranking }}</strong></td>
            <td class="mono">{{ $b['aset']->kode_barang }} / {{ $b['aset']->nup }}</td>
            <td>{{ $b['aset']->nama_barang }}@if ($b['peringatan'])<br><span style="color: #b45309; font-size: 7pt;">⚠ Berfungsi tetapi biaya tinggi</span>@endif</td>
            <td class="num mono">{{ $f4($b['hasil']->yi) }}</td>
            <td class="num">{{ $f2($b['hasil']->skor_relatif) }}</td>
            <td><span class="badge {{ $b['hasil']->rekomendasi->value }}">{{ $b['hasil']->rekomendasi->label() }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if ($tampilKeputusan)
<h2>Keputusan Tindakan Pimpinan</h2>
<table class="data">
    <thead><tr><th class="center" style="width: 30pt">Rank</th><th>Nama Barang</th><th style="width: 62pt">Rekomendasi</th><th style="width: 62pt">Keputusan</th><th style="width: 150pt">Catatan</th></tr></thead>
    <tbody>
        @foreach ($baris as $b)
        <tr>
            <td class="center">{{ $b['hasil']->ranking }}</td>
            <td>{{ $b['aset']->nama_barang }}</td>
            <td><span class="badge {{ $b['hasil']->rekomendasi->value }}">{{ $b['hasil']->rekomendasi->label() }}</span></td>
            <td>@if ($b['keputusan'])<span class="badge {{ $b['keputusan']->tindakan->value }}">{{ $b['keputusan']->tindakan->label() }}</span>@else<span style="color: #9ca3af;">Belum</span>@endif</td>
            <td style="font-size: 8pt;">{{ $b['keputusan']?->catatan ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<p class="catatan">
    Skor relatif = (Y<sub>i</sub> − Y<sub>min</sub>) / (Y<sub>max</sub> − Y<sub>min</sub>) × 100.
    Ambang: Pertahankan ≥ {{ $periode->snapshot_ambang['pertahankan'] ?? '—' }}, Hapus &lt; {{ $periode->snapshot_ambang['perbaiki'] ?? '—' }}, selebihnya Perbaiki.
    Rekomendasi sistem merupakan alat bantu; keputusan akhir ditetapkan oleh pimpinan.
</p>

<table class="ttd"><tr>
    <td style="width: 60%">
        @if (! empty($verifikasi['qr']))
        <div class="verifikasi">
            <img src="{{ $verifikasi['qr'] }}" alt="QR verifikasi">
            <strong>Verifikasi keaslian dokumen</strong><br>
            Pindai kode QR atau buka:<br>{{ $verifikasi['url'] }}<br>
            Kode: <span class="kode">{{ $verifikasi['kode'] }}</span>
        </div>
        @endif
    </td>
    <td>
        Batuceper, {{ now()->translatedFormat('d F Y') }}<br>
        {{ $instansi['jabatan'] }}<br><br><br><br><br>
        <strong style="text-decoration: underline;">{{ $instansi['penandatangan'] }}</strong><br>
        NIP. {{ $instansi['nip'] }}
    </td>
</tr></table>
</body>
</html>
