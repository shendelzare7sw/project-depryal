{{-- @var \App\Imports\AsetImportResult $result --}}
<x-layouts.app title="Pratinjau Import BMD">
    <x-ui.page-header title="Pratinjau Import BMD" subtitle="Periksa hasil validasi sebelum menyimpan" />

    <div class="grid grid-cols-3 gap-2 mb-4">
        <x-ui.stat label="Total" :value="$result->total()" tone="primary" />
        <x-ui.stat label="Valid" :value="count($result->valid)" tone="success" />
        <x-ui.stat label="Error" :value="count($result->invalid)" tone="error" />
    </div>

    @if ($result->invalid !== [])
    <x-ui.card title="Baris bermasalah (tidak akan disimpan)">
        <ul class="divide-y divide-base-200 text-sm">
            @foreach ($result->invalid as $row)
            <li class="py-2">
                <span class="badge badge-error badge-sm mr-1">Baris {{ $row['baris'] }}</span>
                <span class="font-medium">{{ $row['data']['nama_barang'] ?? '(tanpa nama)' }}</span>
                <ul class="list-disc pl-5 text-error text-xs mt-1">
                    @foreach ($row['errors'] as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </li>
            @endforeach
        </ul>
    </x-ui.card>
    @endif

    @if ($result->valid !== [])
    <div class="mt-4">
        <x-ui.card title="Baris valid ({{ count($result->valid) }})">
            <x-ui.responsive-list>
                <x-slot:table>
                    <table class="table table-xs">
                        <thead>
                            <tr><th>Baris</th><th>Kode / NUP</th><th>Nama Barang</th><th>Kategori</th><th>Tgl Perolehan</th><th class="text-right">Nilai Perolehan</th><th class="text-right">Nilai Buku</th><th class="text-center">Sisa UEB</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($result->valid as $row)
                            <tr>
                                <td>{{ $row['baris'] }}</td>
                                <td class="font-mono whitespace-nowrap">{{ $row['data']['kode_barang'] }} / {{ $row['data']['nup'] }}</td>
                                <td>{{ $row['data']['nama_barang'] }}</td>
                                <td>{{ $row['data']['kategori'] }}</td>
                                <td class="whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($row['data']['tanggal_perolehan'])->format('d/m/Y') }}</td>
                                <td class="text-right whitespace-nowrap"><x-rupiah :value="$row['data']['nilai_perolehan']" /></td>
                                <td class="text-right whitespace-nowrap"><x-rupiah :value="$row['data']['nilai_buku']" /></td>
                                <td class="text-center">{{ $row['data']['sisa_ueb'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot:table>
                <x-slot:cards>
                    @foreach ($result->valid as $row)
                    <div class="border border-base-200 rounded-lg p-3 text-sm">
                        <div class="font-medium">{{ $row['data']['nama_barang'] }}</div>
                        <div class="font-mono text-xs opacity-60">Baris {{ $row['baris'] }} · {{ $row['data']['kode_barang'] }} · NUP {{ $row['data']['nup'] }}</div>
                        <div class="text-xs">{{ $row['data']['kategori'] }} · Nilai buku <x-rupiah :value="$row['data']['nilai_buku']" /></div>
                    </div>
                    @endforeach
                </x-slot:cards>
            </x-ui.responsive-list>
        </x-ui.card>
    </div>
    @endif

    <div class="sticky bottom-16 lg:bottom-0 z-10 bg-base-200/95 py-3 mt-2 flex gap-2 justify-end">
        <a href="{{ route('aset.import') }}" class="btn btn-ghost min-h-[44px]">Unggah ulang</a>
        @if ($result->valid !== [])
        <x-confirm-form :action="route('aset.import.confirm')" title="Simpan hasil import?"
            text="{{ count($result->valid) }} baris valid akan disimpan (data dengan kode barang + NUP sama diperbarui). Baris error diabaikan."
            confirm="Ya, simpan" icon="question" class="flex-1 md:flex-none">
            <button type="submit" class="btn btn-primary min-h-[44px] w-full">
                <x-heroicon-o-check class="w-4 h-4" /> Simpan {{ count($result->valid) }} Aset
            </button>
        </x-confirm-form>
        @endif
    </div>
</x-layouts.app>
