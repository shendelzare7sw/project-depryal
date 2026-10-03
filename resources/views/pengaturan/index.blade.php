<x-layouts.app title="Pengaturan" subtitle="Ambang rekomendasi & identitas laporan">
    @php
        $nilai = fn (string $k, mixed $default = '') => old($k, $pengaturan[$k] ?? $default);
    @endphp
    <form method="POST" action="{{ route('pengaturan.update') }}" class="flex min-w-0 flex-col gap-5"
        x-data="{ busy: false, atas: {{ (float) $nilai('ambang_pertahankan', 66.67) }}, bawah: {{ (float) $nilai('ambang_perbaiki', 33.33) }} }" @submit="busy = true">
        @csrf
        @method('PUT')

        @if ($errors->any())
        <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
            <ul class="list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <div class="grid min-w-0 gap-5 xl:grid-cols-2">
            <x-ui.card title="Ambang rekomendasi" icon="adjustments-horizontal" icon-tone="text-amber-500" subtitle="Berdasarkan skor relatif MOORA 0–100">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="ambang_pertahankan" label="Pertahankan bila skor ≥" type="number" step="0.01" min="0" max="100" :value="$nilai('ambang_pertahankan', 66.67)" required x-model.number="atas" />
                    <x-form.input name="ambang_perbaiki" label="Hapus bila skor di bawah" type="number" step="0.01" min="0" max="100" :value="$nilai('ambang_perbaiki', 33.33)" required x-model.number="bawah" />
                </div>
                <div>
                    <p class="mb-2 text-xs font-semibold text-zinc-600">Pratinjau rentang</p>
                    <div class="flex h-3 w-full overflow-hidden rounded-full bg-zinc-100">
                        <span class="h-full bg-rose-500 transition-all" :style="`width: ${Math.max(0, Math.min(100, bawah))}%`"></span>
                        <span class="h-full bg-amber-400 transition-all" :style="`width: ${Math.max(0, Math.min(100, atas) - Math.max(0, bawah))}%`"></span>
                        <span class="h-full flex-1 bg-emerald-500"></span>
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                        <p class="text-rose-700"><strong>Hapus</strong><br><span x-text="'skor < ' + bawah"></span></p>
                        <p class="text-amber-700"><strong>Perbaiki</strong><br><span x-text="bawah + ' – ' + atas"></span></p>
                        <p class="text-emerald-700"><strong>Pertahankan</strong><br><span x-text="'skor ≥ ' + atas"></span></p>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-rose-600" x-show="atas <= bawah" x-cloak>Ambang Pertahankan harus lebih besar dari ambang Hapus.</p>
                </div>
                <p class="flex items-start gap-2 rounded-xl bg-sky-50 p-3 text-xs leading-5 text-sky-900">
                    <x-heroicon-o-information-circle class="h-4 w-4 shrink-0 text-sky-600" />
                    Perubahan ambang berlaku untuk perhitungan berikutnya. Periode yang sudah dihitung tetap memakai ambang saat itu (snapshot).
                </p>
            </x-ui.card>

            <x-ui.card title="Identitas instansi & penandatangan" icon="building-library" subtitle="Dipakai pada kop dan tanda tangan laporan PDF">
                <x-form.input name="nama_instansi" label="Nama instansi" :value="$nilai('nama_instansi')" required maxlength="150" />
                <x-form.textarea name="alamat_instansi" label="Alamat instansi" :value="$nilai('alamat_instansi')" rows="2" maxlength="255" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="nama_penandatangan" label="Nama penandatangan" :value="$nilai('nama_penandatangan')" required maxlength="150" />
                    <x-form.input name="jabatan_penandatangan" label="Jabatan" :value="$nilai('jabatan_penandatangan')" required maxlength="100" />
                    <x-form.input name="nip_penandatangan" label="NIP" :value="$nilai('nip_penandatangan')" maxlength="30" hint="Opsional" />
                </div>
            </x-ui.card>
        </div>

        <x-ui.action-bar>
            <div class="flex items-center justify-between gap-2">
                <p class="hidden text-xs text-zinc-500 md:block">Setiap perubahan tercatat di Audit Log (nilai sebelum & sesudah).</p>
                <x-ui.btn type="submit" tone="primary" icon="check" class="w-full md:w-auto" ::disabled="busy || atas <= bawah">Simpan Pengaturan</x-ui.btn>
            </div>
        </x-ui.action-bar>
    </form>
</x-layouts.app>
