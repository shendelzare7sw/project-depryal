<x-layouts.app title="Cadangan" subtitle="Cadangan database, foto aset, dan berkas laporan">
    @php
        $terakhir = $cadangan[0] ?? null;
        $stats = [
            ['label' => 'Jumlah cadangan', 'value' => count($cadangan), 'icon' => 'archive-box', 'tone' => 'brand'],
            ['label' => 'Cadangan terakhir', 'value' => $terakhir ? $terakhir['waktu']->translatedFormat('d M, H.i') : 'Belum ada', 'icon' => 'clock', 'tone' => $terakhir && $terakhir['waktu']->gt(now()->subDays(2)) ? 'emerald' : 'amber'],
            ['label' => 'Disimpan selama', 'value' => $simpanHari.' hari', 'icon' => 'calendar-days', 'tone' => 'sky'],
        ];
    @endphp

    <x-ui.stat-grid :items="$stats" :columns="3" />

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><x-heroicon-o-archive-box class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Daftar cadangan</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">Otomatis setiap hari pukul 01.00 · 3 cadangan terbaru selalu disimpan</p>
                </div>
            </div>
            <x-confirm-form :action="route('backup.store')" title="Buat cadangan sekarang?" text="Proses dapat memakan waktu beberapa detik hingga menit, tergantung jumlah foto." confirm="Ya, buat" icon="question">
                <x-ui.btn type="submit" tone="primary" icon="plus" class="w-full">Buat Cadangan Sekarang</x-ui.btn>
            </x-confirm-form>
        </header>

        @forelse ($cadangan as $item)
        <div class="flex flex-col gap-3 border-t border-zinc-100 p-4 first:border-t-0 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600"><x-heroicon-o-document-arrow-down class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <p class="truncate font-mono text-xs font-bold text-zinc-800">{{ $item['nama'] }}</p>
                    <p class="text-[11px] text-zinc-500">{{ $item['waktu']->translatedFormat('l, d F Y H:i') }} · {{ \Illuminate\Support\Number::fileSize($item['ukuran'], 1) }}</p>
                </div>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-2 sm:flex">
                <x-ui.btn tone="soft-brand" icon="arrow-down-tray" :href="route('backup.download', $item['nama'])">Unduh</x-ui.btn>
                <x-confirm-form :action="route('backup.destroy', $item['nama'])" method="DELETE" title="Hapus cadangan ini?" text="{{ $item['nama'] }} akan dihapus permanen." confirm="Ya, hapus">
                    <x-ui.btn type="submit" tone="soft-rose" icon="trash" aria-label="Hapus cadangan" />
                </x-confirm-form>
            </div>
        </div>
        @empty
        <x-ui.empty-state icon="archive-box" title="Belum ada cadangan" text="Tekan &quot;Buat Cadangan Sekarang&quot; atau tunggu cadangan otomatis malam ini." />
        @endforelse
    </section>

    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs leading-5 text-amber-900">
        <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-amber-600" />
        <p>Cadangan berisi seluruh data (termasuk akun pengguna). Simpan salinannya di tempat aman di luar server (mis. flashdisk dinas / penyimpanan kantor). Cara memulihkan tertulis di berkas <strong>BACA-SAYA.txt</strong> di dalam ZIP.</p>
    </div>
</x-layouts.app>
