{{--
    Kartu dasar. icon/iconTone = ikon di judul; link + linkLabel = tautan "Lihat semua";
    flush = isi tanpa padding (tabel/daftar). Slot opsional: actions (kanan header), chip (di samping judul).
--}}
@props(['title' => null, 'subtitle' => null, 'icon' => null, 'iconTone' => 'text-brand-600', 'link' => null, 'linkLabel' => 'Lihat semua', 'flush' => false])
<section {{ $attributes->class(['min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]']) }}>
    @if ($title || isset($actions))
    <header @class(['flex items-center justify-between gap-3 border-b border-zinc-100 px-4 py-3 sm:px-5', 'flex-wrap' => isset($actions)])>
        <div class="min-w-0 flex-1">
            <h2 class="flex min-w-0 items-center gap-2 text-sm font-extrabold text-zinc-900 sm:text-base">
                @if ($icon)<x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5 shrink-0 {{ $iconTone }}" />@endif
                <span class="truncate">{{ $title }}</span>
                {{ $chip ?? '' }}
            </h2>
            @if ($subtitle)<p class="mt-0.5 text-xs text-zinc-500">{{ $subtitle }}</p>@endif
        </div>
        @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        @if ($link)<a href="{{ $link }}" class="ml-auto shrink-0 text-xs font-bold text-brand-700 hover:text-brand-900">{{ $linkLabel }}</a>@endif
    </header>
    @endif
    <div @class(['space-y-3 p-4 sm:p-5' => ! $flush])>
        {{ $slot }}
    </div>
</section>
