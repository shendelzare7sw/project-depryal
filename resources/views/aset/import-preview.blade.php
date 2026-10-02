{{-- @var \App\Imports\AsetImportResult $result --}}
<x-layouts.app title="Pratinjau Import BMD" subtitle="Periksa hasil validasi sebelum menyimpan">
    <x-ui.stat-grid :columns="3" :items="[
        ['label' => 'Total baris dibaca', 'value' => $result->total(), 'icon' => 'table-cells', 'tone' => 'brand'],
        ['label' => 'Baris valid', 'value' => count($result->valid), 'icon' => 'check-circle', 'tone' => 'emerald', 'meta' => 'Akan disimpan'],
        ['label' => 'Baris error', 'value' => count($result->invalid), 'icon' => 'x-circle', 'tone' => 'rose', 'meta' => 'Diabaikan'],
    ]" />

    @if ($result->invalid !== [])
    <x-ui.card title="Baris bermasalah" icon="exclamation-triangle" icon-tone="text-rose-500" subtitle="Tidak akan disimpan — perbaiki di Excel lalu unggah ulang bila perlu" flush>
        <x-slot:chip><x-ui.badge tone="error">{{ count($result->invalid) }}</x-ui.badge></x-slot:chip>
        <ul class="divide-y divide-zinc-100">
            @foreach ($result->invalid as $row)
            <li class="flex gap-3 px-4 py-3 sm:px-5">
                <span class="flex h-9 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-rose-50 text-rose-700">
                    <span class="text-[9px] font-bold uppercase">Baris</span>
                    <span class="text-xs font-extrabold leading-none">{{ $row['baris'] }}</span>
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-zinc-900">{{ $row['data']['nama_barang'] ?? '(tanpa nama)' }}</p>
                    <ul class="mt-1 space-y-0.5 text-xs text-rose-700">
                        @foreach ($row['errors'] as $error)<li>• {{ $error }}</li>@endforeach
                    </ul>
                </div>
            </li>
            @endforeach
        </ul>
    </x-ui.card>
    @endif

    @if ($result->valid !== [])
    <x-ui.card title="Baris valid" icon="check-circle" icon-tone="text-emerald-600" flush>
        <x-slot:chip><x-ui.badge tone="success">{{ count($result->valid) }}</x-ui.badge></x-slot:chip>
        <div class="divide-y divide-zinc-100 lg:hidden">
            @foreach ($result->valid as $row)
            <article class="p-4">
                <p class="truncate text-sm font-bold text-zinc-900">{{ $row['data']['nama_barang'] }}</p>
                <p class="mt-0.5 truncate font-mono text-[11px] text-zinc-500">Baris {{ $row['baris'] }} · {{ $row['data']['kode_barang'] }} · NUP {{ $row['data']['nup'] }}</p>
                <div class="mt-2 grid grid-cols-2 gap-2 rounded-xl bg-zinc-50 p-3 text-[11px]">
                    <div class="min-w-0"><p class="text-zinc-400">Kategori</p><p class="truncate font-semibold text-zinc-700">{{ $row['data']['kategori'] }}</p></div>
                    <div class="min-w-0"><p class="text-zinc-400">Nilai buku</p><p class="truncate font-semibold text-zinc-700"><x-rupiah :value="$row['data']['nilai_buku']" /></p></div>
                </div>
            </article>
            @endforeach
        </div>
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[960px] table-fixed text-left text-sm">
                <colgroup><col class="w-16"><col class="w-48"><col><col class="w-[16%]"><col class="w-28"><col class="w-36"><col class="w-36"><col class="w-20"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3 text-center">Baris</th><th class="px-3 py-3">Kode / NUP</th><th class="px-3 py-3">Nama Barang</th><th class="px-3 py-3">Kategori</th><th class="px-3 py-3">Perolehan</th><th class="px-3 py-3 text-right">Nilai Perolehan</th><th class="px-3 py-3 text-right">Nilai Buku</th><th class="px-3 py-3 text-center">Sisa UEB</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($result->valid as $row)
                    <tr class="hover:bg-zinc-50/80">
                        <td class="px-3 py-2.5 text-center text-xs tabular-nums text-zinc-400">{{ $row['baris'] }}</td>
                        <td class="truncate whitespace-nowrap px-3 py-2.5 font-mono text-xs text-zinc-700">{{ $row['data']['kode_barang'] }} / {{ $row['data']['nup'] }}</td>
                        <td class="px-3 py-2.5"><p class="truncate font-semibold text-zinc-800" title="{{ $row['data']['nama_barang'] }}">{{ $row['data']['nama_barang'] }}</p></td>
                        <td class="px-3 py-2.5 text-xs text-zinc-600"><p class="truncate">{{ $row['data']['kategori'] }}</p></td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-xs text-zinc-600">{{ \Illuminate\Support\Carbon::parse($row['data']['tanggal_perolehan'])->format('d/m/Y') }}</td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-right text-xs tabular-nums"><x-rupiah :value="$row['data']['nilai_perolehan']" /></td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-right text-xs font-semibold tabular-nums"><x-rupiah :value="$row['data']['nilai_buku']" /></td>
                        <td class="px-3 py-2.5 text-center text-xs font-bold tabular-nums">{{ $row['data']['sisa_ueb'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

    <div class="sticky bottom-[4.25rem] z-20 -mx-3 border-t border-zinc-200/80 bg-white/95 px-3 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:static lg:mx-0 lg:rounded-2xl lg:border lg:px-5">
        <div class="flex items-center justify-between gap-2">
            <p class="hidden text-xs text-zinc-500 sm:block">Baris error diabaikan. Data dengan kode barang + NUP sama akan diperbarui.</p>
            <div class="flex flex-1 gap-2 sm:flex-none">
                <x-ui.btn tone="white" icon="arrow-path" :href="route('aset.import')">Unggah ulang</x-ui.btn>
                @if ($result->valid !== [])
                <x-confirm-form :action="route('aset.import.confirm')" title="Simpan hasil import?"
                    text="{{ count($result->valid) }} baris valid akan disimpan (data dengan kode barang + NUP sama diperbarui). Baris error diabaikan."
                    confirm="Ya, simpan" icon="question" class="flex-1 sm:flex-none">
                    <x-ui.btn type="submit" tone="primary" icon="check" class="w-full">Simpan {{ count($result->valid) }} Aset</x-ui.btn>
                </x-confirm-form>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
