<!DOCTYPE html>
<html lang="id" data-theme="corporate">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Masuk' }} — SIKASET</title>
    <meta name="description" content="Masuk ke SIKASET, Sistem Pendukung Keputusan Kelayakan Aset BMD Kecamatan Batuceper">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/logo.jpeg') }}">
    @include('layouts.partials.assets')
</head>
<body class="bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{ $slot }}
    </div>
</body>
</html>
