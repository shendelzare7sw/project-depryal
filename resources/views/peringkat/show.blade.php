<x-layouts.app :title="$aset->nama_barang" :subtitle="'Hasil MOORA · '.$periode->nama">
    @php($bisaPutus = auth()->user()->isPimpinan() && $periode->status->value === 'dihitung')

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <a href="{{ route('peringkat.index', $periode) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600 hover:bg-zinc-200" aria-label="Kembali ke peringkat"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
                <x-ui.rank :ranking="$hasil->ranking" size="lg" />
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900 sm:text-lg">{{ $aset->nama_barang }}</h2>
                    <p class="mt-0.5 truncate font-mono text-[11px] text-zinc-500">{{ $aset->kode_barang }} · NUP {{ $aset->nup }} · {{ $aset->kategori?->nama }}</p>
                    <p class="mt-1 text-xs text-zinc-500">Peringkat {{ $hasil->ranking }} dari {{ $total }} aset</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex">
                <x-ui.btn tone="white" icon="chevron-left" :href="$sebelumnya ? route('peringkat.show', [$periode, $sebelumnya]) : null" :disabled="! $sebelumnya">Sebelumnya</x-ui.btn>
                <x-ui.btn tone="white" icon="chevron-right" :href="$berikutnya ? route('peringkat.show', [$periode, $berikutnya]) : null" :disabled="! $berikutnya">Berikutnya</x-ui.btn>
                @if ($bisaPutus)
                <x-ui.btn tone="primary" icon="check-badge" :href="route('keputusan.edit', [$periode, $aset])" class="col-span-2">{{ $keputusan ? 'Ubah Keputusan' : 'Tentukan Tindakan' }}</x-ui.btn>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 border-t border-zinc-100 bg-zinc-50/60 lg:grid-cols-4">
            <div class="border-r border-zinc-100 p-4"><p class="text-[10px] font-bold uppercase tracking-wide text-zinc-500">Skor relatif</p><p class="mt-0.5 text-xl font-extrabold tabular-nums text-zinc-900">{{ number_format($hasil->skor_relatif, 2, ',', '.') }}</p></div>
            <div class="p-4 lg:border-r lg:border-zinc-100"><p class="text-[10px] font-bold uppercase tracking-wide text-zinc-500">Nilai optimasi (Yi)</p><p class="mt-0.5 font-mono text-xl font-extrabold tabular-nums text-zinc-900">{{ number_format($hasil->yi, 4, ',', '.') }}</p></div>
            <div class="border-r border-t border-zinc-100 p-4 lg:border-t-0"><p class="text-[10px] font-bold uppercase tracking-wide text-zinc-500">Rekomendasi sistem</p><x-ui.badge-tindakan :tindakan="$hasil->rekomendasi" class="mt-1.5" /></div>
            <div class="border-t border-zinc-100 p-4 lg:border-t-0"><p class="text-[10px] font-bold uppercase tracking-wide text-zinc-500">Keputusan pimpinan</p>
                @if ($keputusan)<x-ui.badge-tindakan :tindakan="$keputusan->tindakan" class="mt-1.5" />@else<x-ui.badge tone="ghost" class="mt-1.5">Belum diputuskan</x-ui.badge>@endif
            </div>
        </div>
    </section>

    @if ($peringatan)
    <div role="alert" class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-600" />
        <p><strong>{{ \App\Services\Peringkat\PeringatanBiayaTinggi::PESAN }}.</strong> Fungsi aset masih baik (≥ 4) tetapi biaya pemeliharaan berada di skala tertinggi.</p>
    </div>
    @endif

    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="min-w-0 space-y-5">
            <x-ui.card title="Nilai tiap kriteria" icon="scale" subtitle="Bobot sesuai snapshot saat dihitung · kanan: nilai terbobot (v) & ternormalisasi (x*)">
                <x-ui.nilai-kriteria :kriteria="$kriteria" />
                <div class="grid grid-cols-2 gap-2 border-t border-zinc-100 pt-3 text-xs">
                    <div class="rounded-xl bg-emerald-50 p-3"><p class="text-emerald-700">Σ benefit</p><p class="font-mono font-extrabold text-emerald-900">{{ number_format($hasil->detail['benefit_sum'] ?? 0, 4, ',', '.') }}</p></div>
                    <div class="rounded-xl bg-amber-50 p-3"><p class="text-amber-700">Σ cost</p><p class="font-mono font-extrabold text-amber-900">{{ number_format($hasil->detail['cost_sum'] ?? 0, 4, ',', '.') }}</p></div>
                </div>
            </x-ui.card>
            @if ($keputusan)
            <x-ui.card title="Keputusan pimpinan" icon="check-badge" icon-tone="text-emerald-600">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge-tindakan :tindakan="$keputusan->tindakan" />
                    <span class="text-xs text-zinc-500">oleh {{ $keputusan->user?->name }} · {{ $keputusan->updated_at?->translatedFormat('d M Y H:i') }}</span>
                </div>
                @if ($keputusan->catatan)<p class="rounded-xl bg-zinc-50 p-3 text-sm leading-6 text-zinc-700">{{ $keputusan->catatan }}</p>@endif
            </x-ui.card>
            @endif
        </div>
        <x-ui.kondisi-foto :kondisi="$kondisi" :fotos="$fotos" />
    </div>
</x-layouts.app>
