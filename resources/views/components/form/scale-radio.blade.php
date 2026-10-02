{{-- Pilihan skala besar (1–5) per kriteria + rubrik yang muncul di bawah pilihan. name: kriteria[{id}] --}}
@props(['kriteria', 'value' => null, 'disabled' => false])
@php
    $terpilih = old("kriteria.{$kriteria->id}", $value);
    $rubrik = $kriteria->skala->mapWithKeys(fn ($s) => [$s->nilai => ['label' => $s->label, 'deskripsi' => $s->deskripsi]]);
@endphp
<fieldset x-data="{ nilai: @js($terpilih !== null ? (string) $terpilih : ''), rubrik: @js($rubrik) }"
    {{ $attributes->class(['min-w-0 rounded-2xl border border-zinc-200/80 bg-white p-4 shadow-sm shadow-zinc-900/[0.03] sm:p-5']) }}>
    <legend class="sr-only">{{ $kriteria->nama }}</legend>
    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-900 font-mono text-xs font-extrabold text-white">{{ $kriteria->kode }}</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-extrabold text-zinc-900">{{ $kriteria->nama }}</p>
                <p class="text-[11px] text-zinc-500">{{ $kriteria->tipe->label() }} · bobot {{ $kriteria->bobotPersen() }}%</p>
            </div>
        </div>
        <button type="button" x-show="nilai !== ''" x-cloak @click="nilai = ''" @disabled($disabled)
            class="shrink-0 rounded-lg px-2 py-1 text-[11px] font-bold text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700">Kosongkan</button>
    </div>

    {{-- nilai kosong tetap terkirim (dan ditimpa radio terpilih karena berada setelahnya) agar nilai lama bisa dihapus --}}
    <input type="hidden" name="kriteria[{{ $kriteria->id }}]" value="">
    <div class="mt-4 grid grid-cols-5 gap-1.5 sm:gap-2">
        @foreach ($kriteria->skala as $skala)
        <label @class(['group flex min-h-[4.25rem] cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-zinc-200 bg-white px-1 py-2 text-center transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 hover:border-brand-300', 'pointer-events-none opacity-60' => $disabled])>
            <input type="radio" name="kriteria[{{ $kriteria->id }}]" value="{{ $skala->nilai }}" x-model="nilai" class="sr-only" @checked((string) $terpilih === (string) $skala->nilai) @disabled($disabled)>
            <span class="text-xl font-extrabold leading-none text-zinc-800 group-has-[:checked]:text-brand-700">{{ $skala->nilai }}</span>
            <span class="line-clamp-2 text-[10px] font-semibold leading-tight text-zinc-500 group-has-[:checked]:text-brand-700 sm:text-[11px]">{{ $skala->label }}</span>
        </label>
        @endforeach
    </div>

    <div class="mt-3 min-h-[2.75rem] rounded-xl bg-zinc-50 px-3 py-2.5 text-xs leading-5">
        <template x-if="nilai !== ''">
            <p><span class="font-bold text-brand-700" x-text="nilai + ' · ' + (rubrik[nilai]?.label ?? '')"></span> <span class="text-zinc-600" x-text="rubrik[nilai]?.deskripsi ? '— ' + rubrik[nilai].deskripsi : ''"></span></p>
        </template>
        <template x-if="nilai === ''">
            <p class="text-zinc-400">Belum dinilai — pilih skala {{ $kriteria->skala_min }}–{{ $kriteria->skala_maks }}.</p>
        </template>
    </div>
    @error("kriteria.{$kriteria->id}")<p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
</fieldset>
