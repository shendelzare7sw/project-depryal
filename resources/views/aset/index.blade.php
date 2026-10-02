<x-layouts.app title="Data Aset">
    <x-ui.page-header title="Data Aset BMD" subtitle="Barang Milik Daerah — Gedung & Bangunan">
        @if (auth()->user()->isOperator())
        <x-slot:actions>
            <a href="{{ route('aset.create') }}" class="btn btn-primary btn-sm gap-1 min-h-[44px] md:min-h-0 flex-1 md:flex-none">
                <x-heroicon-o-plus class="w-4 h-4" /> Tambah
            </a>
            <a href="{{ route('aset.import') }}" class="btn btn-outline btn-sm gap-1 min-h-[44px] md:min-h-0 flex-1 md:flex-none">
                <x-heroicon-o-arrow-up-tray class="w-4 h-4" /> Import BMD
            </a>
            <a href="{{ route('aset.export') }}" class="btn btn-outline btn-sm gap-1 min-h-[44px] md:min-h-0 flex-1 md:flex-none">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" /> Export
            </a>
        </x-slot:actions>
        @endif
    </x-ui.page-header>

    {{-- Pencarian & filter --}}
    <form method="GET" action="{{ route('aset.index') }}" class="grid grid-cols-2 md:grid-cols-[1fr_14rem_12rem_auto] gap-2 mb-4">
        <label class="input input-bordered flex items-center gap-2 col-span-2 md:col-span-1">
            <x-heroicon-o-magnifying-glass class="w-4 h-4 opacity-50" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode barang" class="grow min-w-0" aria-label="Cari aset">
        </label>
        <select name="kategori" class="select select-bordered w-full min-w-0" aria-label="Filter kategori">
            <option value="">Semua kategori</option>
            @foreach ($kategori as $id => $nama)
            <option value="{{ $id }}" @selected((string) request('kategori') === (string) $id)>{{ $nama }}</option>
            @endforeach
        </select>
        <select name="status" class="select select-bordered w-full min-w-0" aria-label="Filter status">
            <option value="">Semua status</option>
            @foreach (\App\Enums\StatusAset::options() as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-neutral col-span-2 md:col-span-1">Terapkan</button>
    </form>

    @if ($aset->isEmpty())
    <x-ui.card>
        <x-ui.empty-state icon="building-office-2" title="Belum ada data aset"
            text="{{ request()->hasAny(['q', 'kategori', 'status']) ? 'Tidak ada aset yang cocok dengan pencarian/filter.' : 'Tambahkan aset secara manual atau import dari Excel BMD.' }}">
            @if (auth()->user()->isOperator())
            <a href="{{ route('aset.import') }}" class="btn btn-primary btn-sm">Import BMD</a>
            @endif
        </x-ui.empty-state>
    </x-ui.card>
    @else
    <p class="text-xs text-base-content/60 mb-2">Menampilkan {{ $aset->firstItem() }}–{{ $aset->lastItem() }} dari {{ $aset->total() }} aset</p>

    <x-ui.responsive-list>
        <x-slot:table>
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Kode / NUP</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-right">Nilai Buku</th>
                            <th class="text-center">Sisa UEB</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aset as $item)
                        <tr class="hover">
                            <td class="font-mono text-xs whitespace-nowrap">{{ $item->kode_barang }}<br><span class="opacity-60">NUP {{ $item->nup }}</span></td>
                            <td>
                                <div class="font-medium">{{ $item->nama_barang }}</div>
                                <x-ui.badge-perhatian :aset="$item" />
                            </td>
                            <td class="text-xs">{{ $item->kategori?->nama }}</td>
                            <td class="text-right whitespace-nowrap"><x-rupiah :value="$item->nilai_buku" /></td>
                            <td class="text-center">{{ $item->sisa_ueb }} th</td>
                            <td><x-ui.badge-status :status="$item->status" /></td>
                            <td class="text-right">
                                <a href="{{ route('aset.show', $item) }}" class="btn btn-ghost btn-xs">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot:table>
        <x-slot:cards>
            @foreach ($aset as $item)
            <a href="{{ route('aset.show', $item) }}" class="card bg-base-100 shadow-sm border border-base-200 active:bg-base-200">
                <div class="card-body p-4 gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="font-semibold leading-snug">{{ $item->nama_barang }}</h2>
                        <x-ui.badge-status :status="$item->status" />
                    </div>
                    <p class="font-mono text-xs opacity-60">{{ $item->kode_barang }} · NUP {{ $item->nup }}</p>
                    <p class="text-xs">{{ $item->kategori?->nama }}</p>
                    <div class="flex items-center justify-between gap-2 mt-1">
                        <span class="text-sm font-semibold"><x-rupiah :value="$item->nilai_buku" /></span>
                        <span class="text-xs opacity-70">Sisa UEB {{ $item->sisa_ueb }} th</span>
                    </div>
                    <x-ui.badge-perhatian :aset="$item" />
                </div>
            </a>
            @endforeach
        </x-slot:cards>
    </x-ui.responsive-list>

    <div class="mt-4">{{ $aset->links() }}</div>
    @endif
</x-layouts.app>
