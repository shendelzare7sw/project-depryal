{{-- Variabel: $kriteria, $action, $method. Kriteria terkunci (dipakai periode final): tipe & skala tidak bisa diubah. --}}
@php
    $terkunci = $kriteria->exists && $kriteria->isTerkunci();
    $rubrik = $kriteria->skala->keyBy('nilai');
@endphp
<form method="POST" action="{{ $action }}" x-data="{ busy: false }" @submit="busy = true" class="space-y-4">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    @if ($terkunci)
    <div role="alert" class="alert alert-info text-sm">
        <x-heroicon-o-lock-closed class="w-5 h-5" />
        <span>Kriteria ini sudah dipakai pada periode penilaian final. <strong>Tipe dan rentang skala dikunci</strong>; bobot, nama, rubrik, dan status aktif tetap dapat diubah.</span>
    </div>
    @endif

    <x-ui.card title="Data Kriteria">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-2">
            <x-form.input name="kode" label="Kode" :value="$kriteria->kode" required maxlength="10" placeholder="C4" />
            <x-form.input name="urutan" label="Urutan" type="number" :value="$kriteria->urutan" required min="1" />
            <div class="col-span-2">
                <x-form.input name="nama" label="Nama Kriteria" :value="$kriteria->nama" required maxlength="100" />
            </div>
            <div class="col-span-2">
                <x-form.select name="tipe" label="Tipe" :options="\App\Enums\TipeKriteria::options()" :value="$kriteria->tipe?->value" required :disabled="$terkunci" />
            </div>
            <div class="col-span-2">
                <x-form.input name="bobot_persen" label="Bobot (%)" type="number" :value="$kriteria->bobotPersen()" required min="0" max="100" step="0.01" hint="Total kriteria aktif = 100%" />
            </div>
            <x-form.input name="skala_min" label="Skala min" type="number" :value="$kriteria->skala_min" required min="1" max="4" :readonly="$terkunci" />
            <x-form.input name="skala_maks" label="Skala maks" type="number" :value="$kriteria->skala_maks" required min="2" max="5" :readonly="$terkunci" />
            <div class="col-span-2 flex items-end pb-2">
                <label class="label cursor-pointer justify-start gap-3 min-h-[44px]">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="toggle toggle-success" @checked(old('is_active', $kriteria->is_active))>
                    <span class="label-text font-medium">Kriteria aktif</span>
                </label>
            </div>
            <div class="col-span-2 md:col-span-4">
                <x-form.textarea name="keterangan" label="Keterangan" :value="$kriteria->keterangan" rows="2" maxlength="1000" />
            </div>
        </div>
    </x-ui.card>

    <x-ui.card title="Rubrik Skala">
        <p class="text-xs text-base-content/60 -mt-1">Isi label untuk setiap nilai di dalam rentang skala (min–maks). Rubrik tampil saat Operator menginput nilai.</p>
        <div class="space-y-3">
            @foreach (range(1, 5) as $nilai)
            <div class="grid grid-cols-[2.5rem_1fr] md:grid-cols-[2.5rem_14rem_1fr] gap-2 items-start">
                <span class="badge badge-primary badge-lg mt-2 w-10">{{ $nilai }}</span>
                <div>
                    <input type="text" name="skala[{{ $nilai }}][label]" value="{{ old("skala.$nilai.label", $rubrik->get($nilai)?->label) }}"
                        placeholder="Label (mis. Baik)" maxlength="50" aria-label="Label skala {{ $nilai }}"
                        @class(['input input-bordered input-sm w-full min-h-[44px] md:min-h-0', 'input-error' => $errors->has("skala.$nilai.label")])>
                    @error("skala.$nilai.label")<span class="text-xs text-error">{{ $message }}</span>@enderror
                </div>
                <input type="text" name="skala[{{ $nilai }}][deskripsi]" value="{{ old("skala.$nilai.deskripsi", $rubrik->get($nilai)?->deskripsi) }}"
                    placeholder="Deskripsi kondisi" maxlength="255" aria-label="Deskripsi skala {{ $nilai }}"
                    class="input input-bordered input-sm w-full min-h-[44px] md:min-h-0 col-start-2 md:col-start-auto">
            </div>
            @endforeach
        </div>
    </x-ui.card>

    <div class="sticky bottom-16 lg:bottom-0 z-10 bg-base-200/95 py-3 flex gap-2 justify-end">
        <a href="{{ route('kriteria.index') }}" class="btn btn-ghost min-h-[44px]">Batal</a>
        <button type="submit" class="btn btn-primary min-h-[44px] flex-1 md:flex-none" :disabled="busy">
            <span class="loading loading-spinner loading-sm" x-show="busy"></span> Simpan Kriteria
        </button>
    </div>
</form>
