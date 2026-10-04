{{-- Kepala halaman autentikasi: identitas (ponsel), judul, subjudul, ringkasan error pertama & pesan status. --}}
@props(['title', 'subtitle' => null])
<div class="mb-8 flex items-center gap-3 lg:hidden">
    <img src="{{ asset('img/logo.jpeg') }}" alt="Logo Kecamatan Batuceper" class="h-12 w-12 rounded-xl bg-white object-contain p-0.5 ring-1 ring-zinc-200">
    <div>
        <p class="text-lg font-extrabold tracking-tight text-zinc-900">SIKASET</p>
        <p class="text-xs text-zinc-500">SPK Kelayakan Aset BMD · Kec. Batuceper</p>
    </div>
</div>

<h2 class="text-2xl font-extrabold tracking-tight text-zinc-900">{{ $title }}</h2>
@if ($subtitle)<p class="mt-1.5 text-sm leading-relaxed text-zinc-500">{{ $subtitle }}</p>@endif

@if ($errors->any())
<div class="mt-5 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-800">
    <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" /> {{ $errors->first() }}
</div>
@endif
@if (session('status'))
<div class="mt-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-medium text-emerald-800">
    <x-heroicon-o-check-circle class="h-5 w-5 shrink-0 text-emerald-600" /> {{ session('status') }}
</div>
@endif
