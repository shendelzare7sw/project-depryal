<x-layouts.app title="Buat Periode" subtitle="Tentukan nama, tanggal, dan aset yang akan dinilai">
    @php
        $cakupanAwal = old('cakupan', 'semua');
        $jmlPerhatian = $aset->filter(fn ($a) => $a->perlu_perhatian)->count();
        $opsi = [
            'semua' => ['Semua aset aktif', $aset->count().' aset berstatus aktif', 'building-office-2'],
            'kategori' => ['Per kategori', 'Pilih satu atau beberapa kategori', 'tag'],
            'perhatian' => ['Perlu perhatian', $jmlPerhatian.' aset dengan sisa UEB ≤ 3 tahun', 'exclamation-triangle'],
            'pilih' => ['Pilih manual', 'Centang aset satu per satu', 'check-circle'],
        ];
    @endphp
    <form method="POST" action="{{ route('periode.store') }}" x-data="{ cakupan: @js($cakupanAwal), q: '', busy: false }" @submit="busy = true" class="flex min-w-0 flex-col gap-5">
        @csrf
        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
            <header class="flex items-center gap-3 p-4 sm:p-5">
                <a href="{{ route('periode.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Periode penilaian baru</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Setelah dibuat, isi nilai setiap aset lalu hitung MOORA.</p>
                </div>
            </header>
            @if ($errors->any())
            <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800 sm:mx-5">
                <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
                <ul class="list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif
        </section>

        <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
            <x-ui.card title="Identitas periode" icon="calendar-days" icon-tone="text-sky-600">
                <x-form.input name="nama" label="Nama periode" required maxlength="150" :value="'Penilaian Kelayakan BMD '.now()->year" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="tanggal_mulai" label="Tanggal mulai" type="date" required :value="now()->format('Y-m-d')" />
                    <x-form.input name="tanggal_selesai" label="Tanggal selesai" type="date" hint="Opsional" />
                </div>
            </x-ui.card>

            <x-ui.card title="Aset yang dinilai" icon="building-office-2" subtitle="Hanya aset berstatus Aktif · minimal 2 aset">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($opsi as $nilai => [$label, $ket, $icon])
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-3 transition has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50/60">
                        <input type="radio" name="cakupan" value="{{ $nilai }}" x-model="cakupan" class="radio radio-sm radio-primary">
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5 shrink-0 text-brand-600" />
                        <span class="min-w-0"><span class="block text-sm font-bold text-zinc-800">{{ $label }}</span><span class="block truncate text-xs text-zinc-500">{{ $ket }}</span></span>
                    </label>
                    @endforeach
                </div>

                <div x-show="cakupan === 'kategori'" x-cloak class="grid grid-cols-1 gap-2 border-t border-zinc-100 pt-3 sm:grid-cols-2">
                    @foreach ($kategori as $kat)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl bg-zinc-50 px-3 py-2.5">
                        <input type="checkbox" name="kategori_ids[]" value="{{ $kat->id }}" class="checkbox checkbox-sm checkbox-primary" @checked(in_array($kat->id, old('kategori_ids', [])))>
                        <span class="min-w-0 flex-1 truncate text-sm font-semibold text-zinc-700">{{ $kat->nama }}</span>
                        <x-ui.badge>{{ $kat->aset_count }}</x-ui.badge>
                    </label>
                    @endforeach
                </div>

                <div x-show="cakupan === 'pilih'" x-cloak class="space-y-2 border-t border-zinc-100 pt-3">
                    <label class="relative block">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                        <input type="search" x-model="q" placeholder="Cari aset…" class="h-10 w-full rounded-xl border border-zinc-200 pl-10 pr-3 text-xs outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </label>
                    <div class="max-h-80 divide-y divide-zinc-100 overflow-y-auto rounded-xl border border-zinc-200">
                        @foreach ($aset as $item)
                        <label class="flex cursor-pointer items-center gap-3 px-3 py-2.5 hover:bg-zinc-50" x-show="!q || @js(mb_strtolower($item->nama_barang.' '.$item->kode_barang)).includes(q.toLowerCase())">
                            <input type="checkbox" name="aset_ids[]" value="{{ $item->id }}" class="checkbox checkbox-sm checkbox-primary" @checked(in_array($item->id, old('aset_ids', [])))>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-zinc-800">{{ $item->nama_barang }}</span>
                                <span class="block truncate font-mono text-[11px] text-zinc-500">{{ $item->kode_barang }} · NUP {{ $item->nup }} · {{ $item->kategori?->nama }}</span>
                            </span>
                            <x-ui.badge-perhatian :aset="$item" />
                        </label>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="sticky bottom-[4.25rem] z-20 -mx-3 border-t border-zinc-200/80 bg-white/95 px-3 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:static lg:mx-0 lg:rounded-2xl lg:border lg:px-5">
            <div class="flex items-center justify-end gap-2">
                <x-ui.btn tone="white" :href="route('periode.index')">Batal</x-ui.btn>
                <x-ui.btn type="submit" tone="primary" icon="check" class="flex-1 sm:flex-none" ::disabled="busy">Buat Periode</x-ui.btn>
            </div>
        </div>
    </form>
</x-layouts.app>
