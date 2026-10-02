<x-layouts.app title="Tentukan Tindakan" :subtitle="$periode->nama">
    @php
        $rekomendasi = $hasil->rekomendasi->value;
        $pilihanAwal = old('tindakan', $keputusan?->tindakan->value ?? $rekomendasi);
        $nada = [
            'pertahankan' => 'has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-emerald-500/20',
            'perbaiki' => 'has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:ring-amber-500/20',
            'hapus' => 'has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:ring-rose-500/20',
        ];
        $ikon = ['pertahankan' => 'bg-emerald-100 text-emerald-700', 'perbaiki' => 'bg-amber-100 text-amber-700', 'hapus' => 'bg-rose-100 text-rose-700'];
        $terkunci = $periode->status->value !== 'dihitung';
    @endphp

    @if ($terkunci)
    <div class="flex items-start gap-3 rounded-2xl border border-zinc-200 bg-zinc-100 p-4 text-sm text-zinc-700">
        <x-heroicon-o-lock-closed class="h-5 w-5 shrink-0 text-zinc-500" />
        <p>{{ $periode->isFinal() ? 'Periode sudah final — keputusan terkunci.' : 'Periode belum dihitung — keputusan belum dapat diberikan.' }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('keputusan.update', [$periode, $aset]) }}" x-data="{ busy: false, pilihan: @js($pilihanAwal), rekomendasi: @js($rekomendasi) }"
        @submit="busy = true" class="flex min-w-0 flex-col gap-5">
        @csrf
        @method('PUT')

        <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
            <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <a href="{{ route('peringkat.index', $periode) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali ke peringkat"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                    <x-ui.rank :ranking="$hasil->ranking" size="lg" />
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-zinc-900 sm:text-lg">{{ $aset->nama_barang }}</h2>
                        <p class="mt-0.5 truncate font-mono text-[11px] text-zinc-500">{{ $aset->kode_barang }} · NUP {{ $aset->nup }} · {{ $aset->kategori?->nama }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <x-ui.badge tone="primary">Skor {{ number_format($hasil->skor_relatif, 2, ',', '.') }}</x-ui.badge>
                            <x-ui.badge tone="neutral">Yi {{ number_format($hasil->yi, 4, ',', '.') }}</x-ui.badge>
                            @if ($keputusan)<x-ui.badge tone="info">Sudah diputuskan</x-ui.badge>@endif
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex">
                    <x-ui.btn tone="white" icon="chevron-left" :href="$sebelumnya ? route('keputusan.edit', [$periode, $sebelumnya]) : null" :disabled="! $sebelumnya">Sebelumnya</x-ui.btn>
                    <x-ui.btn tone="white" icon="chevron-right" :href="$berikutnya ? route('keputusan.edit', [$periode, $berikutnya]) : null" :disabled="! $berikutnya">Berikutnya</x-ui.btn>
                </div>
            </div>
            @if ($peringatan)
            <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900 sm:mx-5">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-600" />
                <p><strong>{{ \App\Services\Peringkat\PeringatanBiayaTinggi::PESAN }}.</strong></p>
            </div>
            @endif
        </section>

        <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="min-w-0 space-y-5">
                <x-ui.card title="Pilih tindakan" icon="check-badge" icon-tone="text-emerald-600" subtitle="Rekomendasi sistem ditandai — keputusan akhir tetap di tangan Anda">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        @foreach (\App\Enums\TindakanAset::cases() as $t)
                        <label class="relative flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-zinc-200 p-4 md:flex-col md:items-start ring-4 ring-transparent transition hover:border-zinc-300 {{ $nada[$t->value] }}">
                            <input type="radio" name="tindakan" value="{{ $t->value }}" x-model="pilihan" class="sr-only" @checked($pilihanAwal === $t->value)>
                            @if ($t->value === $rekomendasi)
                            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-brand-700 px-2 py-0.5 text-[10px] font-bold text-white"><x-heroicon-s-sparkles class="h-3 w-3" /> Rekomendasi</span>
                            @endif
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $ikon[$t->value] }}"><x-dynamic-component :component="'heroicon-o-'.$t->icon()" class="h-6 w-6" /></span>
                            <span>
                                <span class="block text-base font-extrabold text-zinc-900">{{ $t->label() }}</span>
                                <span class="mt-0.5 block text-xs leading-5 text-zinc-500">{{ $t->keterangan() }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('tindakan')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror

                    <div>
                        <label for="catatan" class="mb-1.5 flex items-baseline justify-between gap-2">
                            <span class="text-sm font-semibold text-zinc-700">Catatan <span class="text-rose-500" x-show="pilihan !== rekomendasi" x-cloak>* wajib (berbeda dari rekomendasi)</span></span>
                            <span class="text-xs text-zinc-400">min. 10 karakter bila berbeda</span>
                        </label>
                        <textarea id="catatan" name="catatan" rows="3" maxlength="1000" :required="pilihan !== rekomendasi" :minlength="pilihan !== rekomendasi ? 10 : 0"
                            placeholder="Alasan/pertimbangan keputusan…"
                            @class(['textarea textarea-bordered w-full border-zinc-300 bg-white text-sm leading-relaxed focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-600/10', 'border-rose-400' => $errors->has('catatan')])>{{ old('catatan', $keputusan?->catatan) }}</textarea>
                        @error('catatan')<p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-rose-600"><x-heroicon-m-exclamation-circle class="h-4 w-4" /> {{ $message }}</p>@enderror
                    </div>
                </x-ui.card>

                <x-ui.card title="Dasar penilaian" icon="scale" :link="route('peringkat.show', [$periode, $aset])" linkLabel="Detail lengkap">
                    <x-ui.nilai-kriteria :kriteria="$kriteria" />
                </x-ui.card>
            </div>
            <x-ui.kondisi-foto :kondisi="$kondisi" :fotos="$fotos" />
        </div>

        @unless ($terkunci)
        <div class="sticky bottom-[4.25rem] z-20 -mx-3 border-t border-zinc-200/80 bg-white/95 px-3 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:bottom-0 lg:mx-0 lg:rounded-2xl lg:border lg:px-5">
            <div class="flex items-center justify-between gap-2">
                <p class="hidden text-xs text-zinc-500 md:block">Setelah disimpan, Anda diarahkan ke aset berikutnya yang belum diputuskan.</p>
                <x-ui.btn type="submit" tone="primary" icon="check" class="w-full md:w-auto" ::disabled="busy">Simpan Keputusan</x-ui.btn>
            </div>
        </div>
        @endunless
    </form>
</x-layouts.app>
