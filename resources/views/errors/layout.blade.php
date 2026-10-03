<!DOCTYPE html>
<html lang="id" data-theme="sikaset">
<head>
    <meta charset="UTF-8">
    <title>@yield('kode') · @yield('judul') — SIKASET</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpeg') }}">
    @include('layouts.partials.assets')
</head>
<body class="flex min-h-screen items-center justify-center bg-base-200 px-5 py-10 font-sans text-zinc-800 antialiased">
    <main class="w-full max-w-md text-center">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-8 ring-brand-50/60">
            @yield('ikon')
        </span>
        <p class="mt-6 text-sm font-extrabold tracking-[0.2em] text-amber-600">GALAT @yield('kode')</p>
        <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-zinc-900">@yield('judul')</h1>
        <p class="mt-2 text-sm leading-6 text-zinc-500">@yield('pesan')</p>
        <div class="mt-6 flex flex-wrap justify-center gap-2">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-white px-4 text-sm font-bold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Kembali</a>
            <a href="{{ url('/') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-brand-700 px-4 text-sm font-bold text-white hover:bg-brand-800">Ke Beranda</a>
        </div>
        <p class="mt-10 text-xs text-zinc-400">SIKASET · Kecamatan Batuceper</p>
    </main>
</body>
</html>
