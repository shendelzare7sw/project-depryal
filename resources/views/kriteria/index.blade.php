<x-layouts.app title="Kriteria">
    <x-ui.page-header title="Kriteria Penilaian" subtitle="Bobot & rubrik skala kriteria metode MOORA">
        @if (auth()->user()->isOperator())
        <x-slot:actions>
            <a href="{{ route('kriteria.create') }}" class="btn btn-primary btn-sm gap-1 min-h-[44px] md:min-h-0 flex-1 md:flex-none">
                <x-heroicon-o-plus class="w-4 h-4" /> Tambah Kriteria
            </a>
        </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.progress-meter :value="$totalBobot" :max="100" label="Total bobot kriteria aktif (%)" strict />
        @if (abs($totalBobot - 100) < 0.0001)
        <p class="text-xs text-success flex items-center gap-1"><x-heroicon-s-check-circle class="w-4 h-4" /> Total bobot sudah 100% — siap dipakai perhitungan MOORA.</p>
        @else
        <p class="text-xs text-error flex items-center gap-1"><x-heroicon-s-exclamation-circle class="w-4 h-4" /> Total bobot harus tepat 100% sebelum perhitungan MOORA (saat ini {{ $totalBobot }}%).</p>
        @endif
    </x-ui.card>

    <div class="mt-4">
        @if ($kriteria->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="list-bullet" title="Belum ada kriteria" text="Tambahkan minimal 2 kriteria aktif dengan total bobot 100%." />
        </x-ui.card>
        @else
        <x-ui.responsive-list>
            <x-slot:table>
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>#</th><th>Kode</th><th>Kriteria</th><th>Tipe</th><th class="text-right">Bobot</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($kriteria as $item)
                            <tr class="hover align-top">
                                <td>{{ $item->urutan }}</td>
                                <td class="font-mono font-semibold">{{ $item->kode }}</td>
                                <td>
                                    <div class="font-medium">{{ $item->nama }}</div>
                                    <x-ui.rubrik-list :kriteria="$item" />
                                </td>
                                <td><span class="badge badge-{{ $item->tipe->color() }} badge-sm">{{ $item->tipe->label() }}</span></td>
                                <td class="text-right font-semibold">{{ $item->bobotPersen() }}%</td>
                                <td>@include('kriteria._status')</td>
                                <td>
                                    @if (auth()->user()->isOperator())
                                    <div class="flex justify-end gap-1">@include('kriteria._actions')</div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-slot:table>
            <x-slot:cards>
                @foreach ($kriteria as $item)
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="card-body p-4 gap-1">
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="font-semibold"><span class="font-mono">{{ $item->kode }}</span> · {{ $item->nama }}</h2>
                            <span class="text-lg font-bold text-primary">{{ $item->bobotPersen() }}%</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <span class="badge badge-{{ $item->tipe->color() }} badge-sm">{{ $item->tipe->label() }}</span>
                            @include('kriteria._status')
                        </div>
                        <x-ui.rubrik-list :kriteria="$item" />
                        @if (auth()->user()->isOperator())
                        <div class="flex gap-2 mt-2">@include('kriteria._actions')</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </x-slot:cards>
        </x-ui.responsive-list>
        @endif
    </div>
</x-layouts.app>
