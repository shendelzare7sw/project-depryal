<x-layouts.app title="Audit Log" subtitle="Jejak perubahan data: siapa, kapan, sebelum & sesudah">
    @php
        $adaFilter = collect(request()->only(['user', 'modul', 'dari', 'sampai']))->filter()->isNotEmpty();
        $warnaEvent = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'error', 'restored' => 'info'];
        $tampil = fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (is_bool($v) ? ($v ? 'ya' : 'tidak') : (string) ($v ?? '—'));
    @endphp

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="border-b border-zinc-200/80 p-4 sm:p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><x-heroicon-o-clipboard-document-list class="h-5 w-5" /></span>
                <div class="min-w-0">
                    <h2 class="text-base font-extrabold text-zinc-900">Riwayat aktivitas</h2>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ $aktivitas->total() }} catatan {{ $adaFilter ? 'sesuai filter' : '' }}</p>
                </div>
            </div>
            <form method="GET" class="mt-4 grid grid-cols-2 gap-2 xl:grid-cols-[minmax(180px,0.5fr)_minmax(160px,0.4fr)_minmax(140px,0.3fr)_minmax(140px,0.3fr)_auto]">
                <select name="user" aria-label="Filter pengguna" class="col-span-2 h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500 xl:col-span-1">
                    <option value="">Semua pengguna</option>
                    @foreach ($pengguna as $id => $nama)<option value="{{ $id }}" @selected((string) request('user') === (string) $id)>{{ $nama }}</option>@endforeach
                </select>
                <select name="modul" aria-label="Filter modul" class="col-span-2 h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500 xl:col-span-1">
                    <option value="">Semua modul</option>
                    @foreach (\App\Enums\ModulAudit::cases() as $m)<option value="{{ $m->value }}" @selected(request('modul') === $m->value)>{{ $m->label() }}</option>@endforeach
                </select>
                <label class="block"><span class="sr-only">Dari tanggal</span><input type="date" name="dari" value="{{ request('dari') }}" class="h-10 w-full rounded-xl border border-zinc-200 px-3 text-xs outline-none focus:border-brand-500"></label>
                <label class="block"><span class="sr-only">Sampai tanggal</span><input type="date" name="sampai" value="{{ request('sampai') }}" class="h-10 w-full rounded-xl border border-zinc-200 px-3 text-xs outline-none focus:border-brand-500"></label>
                <div class="col-span-2 flex gap-2 xl:col-span-1">
                    <x-ui.btn type="submit" tone="dark" icon="funnel" class="flex-1">Filter</x-ui.btn>
                    @if ($adaFilter)<x-ui.btn tone="soft-rose" icon="x-mark" :href="route('audit-log.index')" aria-label="Hapus filter"></x-ui.btn>@endif
                </div>
            </form>
        </header>

        <div class="divide-y divide-zinc-100">
            @forelse ($aktivitas as $a)
            @php
                $modul = \App\Enums\ModulAudit::tryFrom((string) $a->log_name);
                $baru = $a->properties['attributes'] ?? [];
                $lama = $a->properties['old'] ?? [];
            @endphp
            <details id="m-{{ $loop->iteration }}" class="group scroll-mt-24">
                <summary class="flex cursor-pointer list-none items-start gap-3 p-4 hover:bg-zinc-50 sm:px-5 [&::-webkit-details-marker]:hidden">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600">
                        <x-dynamic-component :component="'heroicon-o-'.($modul?->icon() ?? 'clipboard-document-list')" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-1.5">
                            <span class="text-sm font-bold text-zinc-900">{{ $a->description }}</span>
                            @if ($a->event)<x-ui.badge :tone="$warnaEvent[$a->event] ?? 'neutral'">{{ \App\Enums\ModulAudit::kataKerja($a->event) }}</x-ui.badge>@endif
                        </span>
                        <span class="mt-0.5 block text-[11px] text-zinc-500">
                            {{ $a->causer?->name ?? 'Sistem' }} · {{ $modul?->label() ?? $a->log_name }} · {{ $a->created_at?->translatedFormat('d M Y H:i:s') }}
                        </span>
                    </span>
                    @if ($baru || $lama)<x-heroicon-m-chevron-down class="mt-2 h-4 w-4 shrink-0 text-zinc-400 transition group-open:rotate-180" />@endif
                </summary>
                @if ($baru || $lama)
                <div class="px-4 pb-4 sm:px-5 sm:pl-[4.25rem]">
                    <div class="overflow-x-auto rounded-xl border border-zinc-200">
                        <table class="w-full min-w-[480px] text-left text-xs">
                            <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500"><tr><th class="px-3 py-2">Kolom</th><th class="px-3 py-2">Sebelum</th><th class="px-3 py-2">Sesudah</th></tr></thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach (array_unique(array_merge(array_keys($lama), array_keys($baru))) as $kolom)
                                <tr><td class="px-3 py-2 font-mono text-zinc-600">{{ $kolom }}</td><td class="px-3 py-2 text-rose-700">{{ $tampil($lama[$kolom] ?? null) }}</td><td class="px-3 py-2 text-emerald-700">{{ $tampil($baru[$kolom] ?? null) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </details>
            @empty
            <x-ui.empty-state icon="clipboard-document-list" title="Belum ada aktivitas" :text="$adaFilter ? 'Tidak ada catatan yang cocok dengan filter.' : 'Perubahan data akan tercatat otomatis di sini.'" />
            @endforelse
        </div>
        <x-ui.muat-lagi :items="$aktivitas" :langkah="20" satu-daftar />
    </section>
</x-layouts.app>
