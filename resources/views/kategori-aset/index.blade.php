<x-layouts.app title="Kategori Aset">
    <x-ui.page-header title="Kategori Aset" subtitle="Pengelompokan gedung & bangunan">
        <x-slot:actions>
            <a href="{{ route('kategori-aset.create') }}" class="btn btn-primary btn-sm gap-1 min-h-[44px] md:min-h-0 flex-1 md:flex-none">
                <x-heroicon-o-plus class="w-4 h-4" /> Tambah Kategori
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($kategori->isEmpty())
    <x-ui.card>
        <x-ui.empty-state icon="tag" title="Belum ada kategori" text="Tambahkan kategori, atau biarkan import BMD membuatnya otomatis." />
    </x-ui.card>
    @else
    <x-ui.responsive-list>
        <x-slot:table>
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <table class="table table-sm">
                    <thead><tr><th>Kode</th><th>Nama</th><th class="text-center">Jumlah Aset</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($kategori as $item)
                        <tr class="hover">
                            <td class="font-mono text-xs">{{ $item->kode }}</td>
                            <td class="font-medium">{{ $item->nama }}</td>
                            <td class="text-center">{{ $item->aset_count }}</td>
                            <td><div class="flex justify-end gap-1">@include('kategori-aset._actions')</div></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-slot:table>
        <x-slot:cards>
            @foreach ($kategori as $item)
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body p-4 gap-1">
                    <h2 class="font-semibold">{{ $item->nama }}</h2>
                    <p class="text-xs opacity-60"><span class="font-mono">{{ $item->kode }}</span> · {{ $item->aset_count }} aset</p>
                    <div class="flex gap-2 mt-2">@include('kategori-aset._actions')</div>
                </div>
            </div>
            @endforeach
        </x-slot:cards>
    </x-ui.responsive-list>
    @endif
</x-layouts.app>
