{{-- Input halaman autentikasi (h-12, ikon kiri). type=password → tombol lihat/sembunyikan. --}}
@props(['name', 'label', 'icon' => 'user', 'type' => 'text', 'value' => null])
@php($sandi = $type === 'password')
<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-zinc-700">{{ $label }}</label>
    <div class="relative" @if ($sandi) x-data="{ lihat: false }" @endif>
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="pointer-events-none absolute left-3.5 top-3.5 h-5 w-5 text-zinc-400" />
        @if ($sandi)
        <input id="{{ $name }}" name="{{ $name }}" :type="lihat ? 'text' : 'password'" {{ $attributes->class('h-12 w-full rounded-xl border border-zinc-300 bg-white pl-11 pr-12 text-sm outline-none placeholder:text-zinc-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-600/10') }}>
        <button type="button" @click="lihat = !lihat" class="absolute right-1.5 top-1.5 flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700" :aria-label="lihat ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
            <x-heroicon-o-eye class="h-5 w-5" x-show="!lihat" />
            <x-heroicon-o-eye-slash class="h-5 w-5" x-show="lihat" x-cloak />
        </button>
        @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes->class('h-12 w-full rounded-xl border border-zinc-300 bg-white pl-11 pr-3 text-sm outline-none placeholder:text-zinc-400 focus:border-brand-600 focus:ring-4 focus:ring-brand-600/10') }}>
        @endif
    </div>
</div>
