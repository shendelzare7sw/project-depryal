{{-- Variabel: $kriteria, $action, $method. Kriteria terkunci (dipakai periode final): tipe & skala tidak bisa diubah. --}}
@php
    $terkunci = $kriteria->exists && $kriteria->isTerkunci();
    $rubrik = $kriteria->skala->keyBy('nilai');
@endphp
<form method="POST" action="{{ $action }}" x-data="{ busy: false, min: {{ (int) old('skala_min', $kriteria->skala_min) }}, maks: {{ (int) old('skala_maks', $kriteria->skala_maks) }} }"
    @submit="busy = true" class="flex min-w-0 flex-col gap-5">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex items-center gap-3 p-4 sm:p-5">
            <a href="{{ route('kriteria.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
            <div class="min-w-0">
                <h2 class="text-base font-extrabold text-zinc-900">{{ $kriteria->exists ? 'Perbarui kriteria' : 'Kriteria baru' }}</h2>
                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $kriteria->exists ? $kriteria->kode.' — '.$kriteria->nama : 'Bobot dalam persen, rubrik skala 1–5' }}</p>
            </div>
        </header>
        @if ($terkunci)
        <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 p-3 text-xs leading-5 text-sky-900 sm:mx-5">
            <x-heroicon-o-lock-closed class="h-5 w-5 shrink-0 text-sky-600" />
            <p>Kriteria ini sudah dipakai pada periode penilaian final. <strong>Tipe dan rentang skala dikunci</strong>; bobot, nama, rubrik, dan status aktif tetap dapat diubah.</p>
        </div>
        @endif
        @if ($errors->any())
        <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:mx-5">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
            <div><p class="font-bold">Periksa kembali {{ $errors->count() }} isian:</p><ul class="mt-1 list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
        @endif
    </section>

    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
        <x-ui.card title="Data kriteria" icon="scale">
            <div class="grid grid-cols-2 gap-4">
                <x-form.input name="kode" label="Kode" :value="$kriteria->kode" required maxlength="10" placeholder="C4" />
                <x-form.input name="urutan" label="Urutan" type="number" :value="$kriteria->urutan" required min="1" />
                <div class="col-span-2"><x-form.input name="nama" label="Nama kriteria" :value="$kriteria->nama" required maxlength="100" placeholder="Fungsi Aset" /></div>
                <x-form.select name="tipe" label="Tipe" :options="\App\Enums\TipeKriteria::options()" :value="$kriteria->tipe?->value" required :disabled="$terkunci" />
                <x-form.input name="bobot_persen" label="Bobot (%)" type="number" :value="$kriteria->bobotPersen()" required min="0" max="100" step="0.01" />
                <x-form.input name="skala_min" label="Skala min" type="number" :value="$kriteria->skala_min" required min="1" max="4" :readonly="$terkunci" x-model.number="min" />
                <x-form.input name="skala_maks" label="Skala maks" type="number" :value="$kriteria->skala_maks" required min="2" max="5" :readonly="$terkunci" x-model.number="maks" />
                <label class="col-span-2 flex min-h-[3.25rem] cursor-pointer items-center justify-between gap-3 rounded-xl border border-zinc-200 px-4">
                    <span>
                        <span class="block text-sm font-bold text-zinc-800">Kriteria aktif</span>
                        <span class="block text-xs text-zinc-500">Hanya kriteria aktif yang dihitung & dijumlah bobotnya</span>
                    </span>
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="toggle toggle-success" @checked(old('is_active', $kriteria->is_active))>
                </label>
                <div class="col-span-2"><x-form.textarea name="keterangan" label="Keterangan" :value="$kriteria->keterangan" rows="3" maxlength="1000" placeholder="Penjelasan singkat maksud kriteria" /></div>
            </div>
        </x-ui.card>

        <x-ui.card title="Rubrik skala" icon="list-bullet" icon-tone="text-violet-500" subtitle="Label wajib untuk setiap nilai di dalam rentang skala">
            <div class="space-y-2.5">
                @foreach (range(1, 5) as $nilai)
                <div class="grid grid-cols-[2.75rem_minmax(0,1fr)] gap-2 rounded-xl border border-zinc-200 p-2.5 transition md:grid-cols-[2.75rem_12rem_minmax(0,1fr)]"
                    :class="{{ $nilai }} < min || {{ $nilai }} > maks ? 'opacity-40' : ''">
                    <span class="row-span-2 flex h-11 items-center justify-center rounded-lg bg-brand-700 text-base font-extrabold text-white md:row-span-1">{{ $nilai }}</span>
                    <div>
                        <input type="text" name="skala[{{ $nilai }}][label]" value="{{ old("skala.$nilai.label", $rubrik->get($nilai)?->label) }}"
                            placeholder="Label (mis. Baik)" maxlength="50" aria-label="Label skala {{ $nilai }}"
                            @class(['h-11 w-full rounded-lg border bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20', 'border-rose-400' => $errors->has("skala.$nilai.label"), 'border-zinc-200' => ! $errors->has("skala.$nilai.label")])>
                        @error("skala.$nilai.label")<p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <input type="text" name="skala[{{ $nilai }}][deskripsi]" value="{{ old("skala.$nilai.deskripsi", $rubrik->get($nilai)?->deskripsi) }}"
                        placeholder="Deskripsi kondisi" maxlength="255" aria-label="Deskripsi skala {{ $nilai }}"
                        class="h-11 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <div class="sticky bottom-[4.25rem] z-20 -mx-3 border-t border-zinc-200/80 bg-white/95 px-3 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:static lg:mx-0 lg:rounded-2xl lg:border lg:px-5">
        <div class="flex items-center justify-end gap-2">
            <x-ui.btn tone="white" :href="route('kriteria.index')">Batal</x-ui.btn>
            <x-ui.btn type="submit" tone="primary" icon="check" class="flex-1 sm:flex-none" ::disabled="busy">Simpan Kriteria</x-ui.btn>
        </div>
    </div>
</form>
