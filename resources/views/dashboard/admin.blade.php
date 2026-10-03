<x-layouts.app title="Beranda" subtitle="Statistik sistem SIKASET">
    @php($user = auth()->user())

    <x-ui.hero :eyebrow="'SIKASET · '.now()->translatedFormat('l, d F Y')" :title="'Halo, '.$user->name.'!'"
        subtitle="Kelola akun pengguna, ambang rekomendasi, dan pantau jejak aktivitas sistem.">
        <x-slot:actions>
            <x-ui.btn tone="amber" icon="users" :href="route('pengguna.index')">Kelola Pengguna</x-ui.btn>
            <x-ui.btn tone="glass" icon="adjustments-horizontal" :href="route('pengaturan.index')">Pengaturan</x-ui.btn>
            @if (Route::has('audit-log.index'))<x-ui.btn tone="glass" icon="clipboard-document-list" :href="route('audit-log.index')">Audit Log</x-ui.btn>@endif
        </x-slot:actions>
    </x-ui.hero>

    <x-ui.stat-grid :items="$stats" />

    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-5">
            <x-ui.card title="Aktivitas terbaru" icon="clipboard-document-list" icon-tone="text-sky-600" :link="Route::has('audit-log.index') ? route('audit-log.index') : null" flush>
                <div class="divide-y divide-zinc-100">
                    @forelse ($aktivitas as $a)
                    <div class="flex items-start gap-3 px-4 py-3 sm:px-5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-xs font-extrabold text-sky-700">{{ strtoupper(mb_substr($a->causer?->name ?? 'S', 0, 1)) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-zinc-800">{{ $a->description }}</p>
                            <p class="truncate text-[11px] text-zinc-500">{{ $a->causer?->name ?? 'Sistem' }} · {{ class_basename((string) $a->subject_type) }} · {{ $a->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <x-ui.empty-state icon="clipboard-document-list" title="Belum ada aktivitas" text="Perubahan data aset, kriteria, periode, nilai, keputusan, dan pengguna akan tercatat di sini." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card title="Periode penilaian" icon="calendar-days" :link="route('periode.index')" flush>
                <div class="divide-y divide-zinc-100">
                    @forelse ($periode as $p)
                    <a href="{{ route('periode.show', $p) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 sm:px-5">
                        <span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold text-zinc-800">{{ $p->nama }}</span><span class="block text-[11px] text-zinc-500">{{ $p->aset_count }} aset · {{ $p->tanggal_mulai?->translatedFormat('d M Y') }}</span></span>
                        <x-ui.badge-status :status="$p->status" />
                    </a>
                    @empty
                    <p class="px-5 py-6 text-xs text-zinc-500">Belum ada periode penilaian.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <aside class="min-w-0 space-y-5">
            <x-ui.card title="Pengguna per role" icon="users" icon-tone="text-violet-500" :link="route('pengguna.index')" linkLabel="Kelola">
                <ul class="space-y-3">
                    @foreach ($roles as $r)
                    <li>
                        <div class="flex items-center justify-between text-xs"><x-ui.badge :tone="$r['role']->color()">{{ $r['role']->label() }}</x-ui.badge><span class="font-bold tabular-nums text-zinc-800">{{ $r['aktif'] }}/{{ $r['jumlah'] }} aktif</span></div>
                        <progress class="progress progress-primary mt-1.5 h-1.5 w-full bg-zinc-100" value="{{ $r['aktif'] }}" max="{{ max(1, $r['jumlah']) }}"></progress>
                    </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card title="Ambang rekomendasi" icon="adjustments-horizontal" icon-tone="text-amber-500" :link="route('pengaturan.index')" linkLabel="Ubah">
                <div class="flex h-3 w-full overflow-hidden rounded-full">
                    <span class="h-full bg-rose-500" style="width: {{ $ambang['perbaiki'] }}%"></span>
                    <span class="h-full bg-amber-400" style="width: {{ max(0, $ambang['pertahankan'] - $ambang['perbaiki']) }}%"></span>
                    <span class="h-full flex-1 bg-emerald-500"></span>
                </div>
                <ul class="space-y-1.5 text-xs text-zinc-600">
                    <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Pertahankan</span><span class="font-bold">skor ≥ {{ $ambang['pertahankan'] }}</span></li>
                    <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span>Perbaiki</span><span class="font-bold">{{ $ambang['perbaiki'] }} – {{ $ambang['pertahankan'] }}</span></li>
                    <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-rose-500"></span>Hapus</span><span class="font-bold">skor &lt; {{ $ambang['perbaiki'] }}</span></li>
                </ul>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
