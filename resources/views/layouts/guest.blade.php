<!DOCTYPE html>
<html lang="id" data-theme="sikaset">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Masuk' }} — SIKASET</title>
    <meta name="description" content="Masuk ke SIKASET, Sistem Pendukung Keputusan Kelayakan Aset BMD Kecamatan Batuceper">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpeg') }}">
    @include('layouts.partials.assets')
</head>
<body class="min-h-screen bg-white font-sans text-zinc-800 antialiased">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
        {{-- Panel identitas (desktop) --}}
        <aside class="relative hidden overflow-hidden bg-brand-950 p-10 text-white lg:flex lg:flex-col xl:p-14">
            <span class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-brand-600/30" aria-hidden="true"></span>
            <span class="pointer-events-none absolute -bottom-32 right-0 h-80 w-80 rounded-full bg-amber-300/15" aria-hidden="true"></span>

            <div class="relative flex items-center gap-3">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Logo Kecamatan Batuceper" class="h-12 w-12 rounded-xl bg-white object-contain p-0.5">
                <div>
                    <p class="text-lg font-extrabold tracking-tight !text-white">SIKASET</p>
                    <p class="text-xs text-white/60">Pemerintah Kecamatan Batuceper</p>
                </div>
            </div>

            <div class="relative mt-auto max-w-xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/85 ring-1 ring-inset ring-white/15">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span> Sistem Pendukung Keputusan · Metode MOORA
                </p>
                <h1 class="mt-5 text-4xl font-extrabold leading-[1.15] tracking-tight !text-white xl:text-5xl">
                    Penilaian kelayakan aset daerah yang <span class="text-amber-300">terukur</span> &amp; transparan.
                </h1>
                <p class="mt-4 text-sm leading-relaxed text-white/65">
                    Nilai gedung &amp; bangunan Barang Milik Daerah, dapatkan peringkat objektif, dan tetapkan tindakan:
                    pertahankan, perbaiki, atau hapus.
                </p>
                <div class="mt-10 grid grid-cols-3 gap-3">
                    @foreach ([['circle-stack', 'Data BMD', 'Import Excel'], ['calculator', 'Hitung MOORA', 'Otomatis'], ['check-badge', 'Keputusan', 'Oleh pimpinan']] as [$icon, $judul, $ket])
                    <div class="rounded-2xl bg-white/[0.06] p-4 ring-1 ring-inset ring-white/10">
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6 text-amber-300" />
                        <p class="mt-3 text-sm font-bold !text-white">{{ $judul }}</p>
                        <p class="text-xs text-white/55">{{ $ket }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Form --}}
        <main class="flex items-center justify-center bg-base-200 px-5 py-10 sm:px-10 lg:bg-white">
            <div class="w-full max-w-sm">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
