<x-layouts.guest title="Verifikasi Dokumen">
    <div x-data="{ busy: false }">
        <x-auth.header title="Verifikasi dokumen" subtitle="Pemeriksaan keaslian laporan yang dicetak dari SIKASET." />

        @if ($laporan)
        <div class="mt-6 overflow-hidden rounded-2xl border border-emerald-200 bg-white">
            <div class="flex items-center gap-3 bg-emerald-50 p-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white"><x-heroicon-o-check-badge class="h-6 w-6" /></span>
                <div class="min-w-0">
                    <p class="text-sm font-extrabold text-emerald-900">Dokumen terdaftar</p>
                    <p class="truncate font-mono text-[11px] text-emerald-800">Kode {{ $laporan->kode_verifikasi }}</p>
                </div>
            </div>
            <dl class="divide-y divide-zinc-100 text-sm">
                @foreach ([
                    'Jenis laporan' => $laporan->jenis->label().' ('.$laporan->format->label().')',
                    'Periode' => $laporan->periode?->nama ?? '—',
                    'Dicetak oleh' => $laporan->user?->name ?? '—',
                    'Tanggal cetak' => $laporan->created_at?->translatedFormat('d F Y H:i'),
                ] as $label => $nilai)
                <div class="grid grid-cols-[7.5rem_minmax(0,1fr)] gap-2 px-4 py-2.5">
                    <dt class="text-xs font-semibold text-zinc-500">{{ $label }}</dt>
                    <dd class="min-w-0 break-words text-xs font-bold text-zinc-800">{{ $nilai }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        @if (session()->has('hasil_cek'))
        <div @class(['mt-4 flex items-start gap-2.5 rounded-xl border p-3 text-xs font-medium', 'border-emerald-200 bg-emerald-50 text-emerald-800' => session('hasil_cek'), 'border-rose-200 bg-rose-50 text-rose-800' => ! session('hasil_cek')])>
            @if (session('hasil_cek'))
            <x-heroicon-o-shield-check class="h-5 w-5 shrink-0" /> Berkas identik dengan arsip SIKASET — isi tidak diubah.
            @else
            <x-heroicon-o-shield-exclamation class="h-5 w-5 shrink-0" /> Berkas BERBEDA dari arsip SIKASET. Dokumen mungkin telah diubah atau bukan berkas asli.
            @endif
        </div>
        @endif

        @if ($laporan->sha256)
        <form method="POST" action="{{ route('verifikasi.cek', $laporan->kode_verifikasi) }}" enctype="multipart/form-data" class="mt-5 space-y-3 rounded-2xl border border-zinc-200 bg-white p-4" @submit="busy = true">
            @csrf
            <p class="text-sm font-bold text-zinc-800">Periksa berkas digital (opsional)</p>
            <p class="text-xs leading-5 text-zinc-500">Unggah berkas PDF/Excel yang Anda terima untuk memastikan isinya sama persis dengan arsip. Berkas tidak disimpan.</p>
            <input type="file" name="berkas" required class="file-input file-input-bordered file-input-sm w-full">
            <x-auth.submit>Periksa Berkas</x-auth.submit>
        </form>
        @endif
        @else
        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900">
            <x-heroicon-o-x-circle class="h-6 w-6 shrink-0 text-rose-600" />
            <div>
                <p class="font-extrabold">Dokumen tidak ditemukan</p>
                <p class="mt-1 text-xs leading-5">Kode <span class="font-mono font-bold">{{ \Illuminate\Support\Str::limit($kode, 32) }}</span> tidak terdaftar di SIKASET. Dokumen kemungkinan bukan cetakan resmi.</p>
            </div>
        </div>
        @endif

        <p class="mt-10 text-center text-xs text-zinc-400">© {{ date('Y') }} SIKASET · Pemerintah Kecamatan Batuceper</p>
    </div>
</x-layouts.guest>
