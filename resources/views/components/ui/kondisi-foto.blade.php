{{-- Kartu kondisi & foto aset pada satu periode. --}}
@props(['kondisi' => null, 'fotos'])
<x-ui.card title="Kondisi & foto" icon="camera" icon-tone="text-violet-500" {{ $attributes }}>
    <p @class(['rounded-xl p-3 text-xs leading-5', 'bg-zinc-50 text-zinc-700' => $kondisi, 'bg-zinc-50 text-zinc-400' => ! $kondisi])>{{ $kondisi ?: 'Operator belum menuliskan deskripsi kondisi.' }}</p>
    @if ($fotos->isNotEmpty())
    <div class="grid grid-cols-3 gap-2">
        @foreach ($fotos as $foto)
        <a href="{{ $foto->url }}" target="_blank" rel="noopener" class="overflow-hidden rounded-xl bg-zinc-100"><img src="{{ $foto->url }}" alt="Foto kondisi" class="aspect-square w-full object-cover transition hover:scale-105" loading="lazy"></a>
        @endforeach
    </div>
    @else
    <p class="flex items-center gap-2 text-xs text-zinc-400"><x-heroicon-o-photo class="h-4 w-4" /> Belum ada foto pada periode ini.</p>
    @endif
</x-ui.card>
