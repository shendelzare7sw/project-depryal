<x-layouts.app :title="$periode->nama" subtitle="Detail periode penilaian">
    @php
        $operator = auth()->user()->isOperator();
        $status = $periode->status->value;
        $bisaHitung = $hambatan === [];
        $sudahHitung = in_array($status, ['dihitung', 'final'], true);
        $filterAktif = request('status');
        $pesan = [
            'draft' => 'Isi nilai semua kriteria untuk setiap aset. Status otomatis menjadi Dinilai saat semua lengkap.',
            'dinilai' => 'Semua nilai lengkap. Jalankan Hitung MOORA untuk menghasilkan peringkat & rekomendasi.',
            'dihitung' => 'Hasil MOORA tersedia. Pimpinan dapat meninjau peringkat dan menetapkan tindakan.',
            'final' => 'Periode final — seluruh data terkunci.',
        ][$status];
        $rentang = $periode->tanggal_mulai?->translatedFormat('d M Y').($periode->tanggal_selesai ? ' – '.$periode->tanggal_selesai->translatedFormat('d M Y') : '');
    @endphp

    <x-ui.hero :eyebrow="'Periode · '.$rentang"
        :title="$periode->nama"
        :subtitle="$pesan">
        <x-slot:actions>
            <x-ui.badge :tone="$periode->status->color()" dot class="bg-white">{{ $periode->status->label() }}</x-ui.badge>
            @if ($operator && ! $periode->isFinal())
                @if ($bisaHitung)
                <x-confirm-form :action="route('periode.hitung', $periode)" title="Hitung MOORA sekarang?"
                    text="{{ $sudahHitung ? 'Hasil & keputusan sebelumnya akan diganti dengan hasil perhitungan baru.' : 'Peringkat dan rekomendasi akan dibuat untuk semua aset dalam periode.' }}"
                    confirm="Ya, hitung" icon="question">
                    <x-ui.btn type="submit" tone="amber" icon="calculator">{{ $sudahHitung ? 'Hitung Ulang MOORA' : 'Hitung MOORA' }}</x-ui.btn>
                </x-confirm-form>
                @else
                <x-ui.btn tone="glass" icon="calculator" disabled title="{{ implode(' ', $hambatan) }}">Hitung MOORA</x-ui.btn>
                @endif
                <x-ui.btn tone="glass" icon="pencil-square" :href="route('periode.edit', $periode)">Ubah</x-ui.btn>
            @endif
            @if ($sudahHitung)
            <x-ui.btn tone="glass" icon="chart-bar" :href="route('peringkat.index', $periode)">Lihat Peringkat</x-ui.btn>
            @endif
        </x-slot:actions>
        <x-slot:aside>
            <div class="w-full rounded-2xl bg-white/10 p-4 ring-1 ring-inset ring-white/20 lg:w-64">
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-100">Progres nilai</p>
                <p class="mt-1 text-3xl font-extrabold !text-white">{{ $progres['persen'] }}<span class="text-base font-bold text-brand-100">%</span></p>
                <progress class="progress mt-3 h-2 w-full bg-white/20 [&::-webkit-progress-value]:bg-amber-300 [&::-moz-progress-bar]:bg-amber-300" value="{{ $progres['terisi'] }}" max="{{ max(1, $progres['diperlukan']) }}"></progress>
                <p class="mt-2 text-xs text-brand-50/90">{{ $progres['terisi'] }}/{{ $progres['diperlukan'] }} nilai · {{ $progres['aset_lengkap'] }}/{{ $progres['total_aset'] }} aset lengkap</p>
            </div>
        </x-slot:aside>
    </x-ui.hero>

    @if ($operator && ! $bisaHitung && ! $periode->isFinal())
    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <x-heroicon-o-information-circle class="h-5 w-5 shrink-0 text-amber-600" />
        <div><p class="font-bold">Hitung MOORA belum bisa dijalankan:</p><ul class="mt-1 list-disc space-y-0.5 pl-4 text-xs">@foreach ($hambatan as $h)<li>{{ $h }}</li>@endforeach</ul></div>
    </div>
    @endif

    @if ($status === 'dihitung' && $operator)
    <div class="flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
        <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-sky-600" />
        <p>Periode sudah dihitung. <strong>Mengubah nilai akan menghapus hasil MOORA dan keputusan pimpinan</strong> pada periode ini — perhitungan harus dijalankan ulang.</p>
    </div>
    @endif

    @if ($periode->isFinal())
    <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_26rem]">
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            <x-heroicon-o-lock-closed class="h-5 w-5 shrink-0 text-emerald-600" />
            <p>Periode difinalisasi {{ $periode->difinalisasi_pada?->translatedFormat('d F Y H:i') }}. Nilai, hasil, dan keputusan terkunci.</p>
        </div>
        @if ($operator)
        <x-ui.card title="Buka kembali periode" icon="lock-open" icon-tone="text-amber-500" subtitle="Alasan wajib, tercatat & diberitahukan ke pimpinan">
            <x-confirm-form :action="route('periode.buka-kembali', $periode)" title="Buka kembali periode final?"
                text="Status kembali menjadi Dihitung sehingga keputusan dapat ditinjau ulang." confirm="Ya, buka kembali" icon="warning" :danger="true" class="space-y-3">
                <x-form.textarea name="alasan" label="Alasan" rows="2" required minlength="10" maxlength="500" placeholder="Mis. koreksi nilai biaya pemeliharaan Posyandu Mawar" />
                <x-ui.btn type="submit" tone="soft-amber" icon="lock-open" class="w-full">Buka Kembali</x-ui.btn>
            </x-confirm-form>
        </x-ui.card>
        @endif
    </div>
    @endif

    @if ($periode->alasan_buka_kembali)
    <p class="rounded-xl bg-zinc-100 px-4 py-2.5 text-xs text-zinc-600"><strong>Catatan buka kembali:</strong> {{ $periode->alasan_buka_kembali }}</p>
    @endif

    {{-- Daftar aset --}}
    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="border-b border-zinc-200/80 p-4 sm:p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700"><x-heroicon-o-clipboard-document-check class="h-5 w-5" /></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-zinc-900">Aset dalam periode</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">{{ $aset->count() }} dari {{ $totalAset }} aset · {{ $kriteriaAktif }} kriteria aktif per aset</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-1 rounded-xl bg-zinc-100 p-1 text-xs font-bold">
                    @foreach (['' => 'Semua', 'belum' => 'Belum', 'lengkap' => 'Lengkap'] as $val => $label)
                    <a href="{{ route('periode.show', [$periode, 'status' => $val ?: null, 'q' => request('q')]) }}" @class(['flex h-9 items-center justify-center rounded-lg px-3', 'bg-white text-zinc-900 shadow-sm' => (string) $filterAktif === $val, 'text-zinc-500 hover:text-zinc-800' => (string) $filterAktif !== $val])>{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <form method="GET" class="mt-3 flex gap-2">
                <input type="hidden" name="status" value="{{ $filterAktif }}">
                <label class="relative flex-1">
                    <span class="sr-only">Cari aset</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode barang…" class="h-10 w-full rounded-xl border border-zinc-200 pl-10 pr-3 text-xs outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </label>
                <x-ui.btn type="submit" tone="dark" icon="magnifying-glass" compact>Cari</x-ui.btn>
            </form>
        </header>

        @php($bisaNilai = $operator && ! $periode->isFinal())
        <div class="divide-y divide-zinc-100 lg:hidden">
            @forelse ($aset as $item)
            @php($lengkap = $item->nilai_terisi >= $kriteriaAktif)
            <article class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $item->nama_barang }}</h3>
                        <p class="mt-1 truncate font-mono text-[11px] text-zinc-500">{{ $item->kode_barang }} · NUP {{ $item->nup }}</p>
                    </div>
                    <x-ui.badge :tone="$lengkap ? 'success' : 'ghost'" dot>{{ $lengkap ? 'Lengkap' : 'Belum' }}</x-ui.badge>
                </div>
                <div class="mt-3 rounded-xl bg-zinc-50 p-3">
                    <div class="flex justify-between text-[11px]"><span class="text-zinc-500">Nilai terisi</span><span class="font-bold tabular-nums text-zinc-800">{{ $item->nilai_terisi }}/{{ $kriteriaAktif }} · {{ $item->foto_periode }} foto</span></div>
                    <progress @class(['progress mt-2 h-1.5 w-full bg-zinc-200', 'progress-success' => $lengkap, 'progress-primary' => ! $lengkap]) value="{{ $item->nilai_terisi }}" max="{{ max(1, $kriteriaAktif) }}"></progress>
                </div>
                @if ($bisaNilai)
                <x-ui.btn :tone="$lengkap ? 'soft-brand' : 'primary'" :icon="$lengkap ? 'pencil-square' : 'clipboard-document-check'" :href="route('penilaian.edit', [$periode, $item])" class="mt-3 w-full">{{ $lengkap ? 'Ubah Nilai' : 'Input Nilai' }}</x-ui.btn>
                @endif
            </article>
            @empty
            <x-ui.empty-state icon="magnifying-glass" title="Tidak ada aset" text="Tidak ada aset yang cocok dengan filter." />
            @endforelse
        </div>

        @if ($aset->isNotEmpty())
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[900px] table-fixed text-left text-sm">
                <colgroup><col class="w-12"><col><col class="w-[18%]"><col class="w-48"><col class="w-20"><col class="w-28"><col class="w-36"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3 text-center">No</th><th class="px-3 py-3">Aset</th><th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Nilai terisi</th><th class="px-3 py-3 text-center">Foto</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($aset as $item)
                    @php($lengkap = $item->nilai_terisi >= $kriteriaAktif)
                    <tr class="hover:bg-zinc-50/80">
                        <td class="px-3 py-3 text-center text-xs tabular-nums text-zinc-400">{{ $loop->iteration }}</td>
                        <td class="px-3 py-3"><p class="truncate font-bold text-zinc-800" title="{{ $item->nama_barang }}">{{ $item->nama_barang }}</p><p class="truncate font-mono text-[11px] text-zinc-500">{{ $item->kode_barang }} · NUP {{ $item->nup }}</p></td>
                        <td class="px-3 py-3 text-xs text-zinc-600"><p class="truncate">{{ $item->kategori?->nama }}</p></td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <progress @class(['progress h-1.5 flex-1 bg-zinc-200', 'progress-success' => $lengkap, 'progress-primary' => ! $lengkap]) value="{{ $item->nilai_terisi }}" max="{{ max(1, $kriteriaAktif) }}"></progress>
                                <span class="w-10 text-right text-xs font-bold tabular-nums text-zinc-700">{{ $item->nilai_terisi }}/{{ $kriteriaAktif }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center text-xs tabular-nums text-zinc-600">{{ $item->foto_periode }}</td>
                        <td class="px-3 py-3"><x-ui.badge :tone="$lengkap ? 'success' : 'ghost'" dot>{{ $lengkap ? 'Lengkap' : 'Belum' }}</x-ui.badge></td>
                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-1.5">
                                <x-ui.table-action :href="route('aset.show', $item)" tone="view" icon="eye" label="Detail aset" />
                                @if ($bisaNilai)
                                <x-ui.table-action :href="route('penilaian.edit', [$periode, $item])" :tone="$lengkap ? 'edit' : 'success'" :icon="$lengkap ? 'pencil-square' : 'clipboard-document-check'" :label="$lengkap ? 'Ubah nilai' : 'Input nilai'" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>
</x-layouts.app>
