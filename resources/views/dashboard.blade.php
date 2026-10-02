<x-layouts.app title="Beranda">
    <x-ui.page-header title="Selamat Datang, {{ auth()->user()->name }}"
        subtitle="Sistem Pendukung Keputusan Kelayakan Aset — Kecamatan Batuceper" />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <x-ui.stat
            label="Total Aset"
            :value="$stats['total_aset']"
            icon="building-office-2"
            tone="primary"
        />
        <x-ui.stat
            label="Periode Aktif"
            :value="$stats['periode_aktif']"
            icon="calendar-days"
            tone="secondary"
        />
        <x-ui.stat
            label="Total Periode"
            :value="$stats['total_periode']"
            icon="chart-bar"
            tone="accent"
        />
    </div>

    {{-- Info Banner --}}
    @auth
    @if (auth()->user()->role->value === 'pimpinan')
    <x-ui.card>
        <div class="flex items-start gap-4">
            <x-heroicon-o-information-circle class="w-8 h-8 text-info shrink-0 mt-0.5" />
            <div>
                <p class="font-semibold text-base-content">Tentang SIKASET</p>
                <p class="text-sm text-base-content/70 mt-1">
                    SIKASET membantu Pimpinan meninjau hasil penilaian kelayakan Barang Milik Daerah
                    menggunakan metode <strong>MOORA</strong>. Keputusan akhir sepenuhnya berada di tangan Pimpinan;
                    sistem ini hanya sebagai alat bantu analisis.
                </p>
            </div>
        </div>
    </x-ui.card>
    @else
    <x-ui.card title="Panduan Cepat">
        <ol class="list-decimal list-inside space-y-1.5 text-sm text-base-content/80 mt-1">
            <li>Pastikan <strong>Data Aset</strong> & <strong>Kriteria</strong> sudah diisi dengan benar.</li>
            <li>Buat <strong>Periode Penilaian</strong> baru, lalu masukkan nilai setiap aset.</li>
            <li>Jalankan <strong>Hitung MOORA</strong> untuk menghasilkan peringkat otomatis.</li>
            <li>Finalisasi periode, lalu ekspor <strong>Laporan</strong> PDF/Excel.</li>
        </ol>
    </x-ui.card>
    @endif
    @endauth
</x-layouts.app>
