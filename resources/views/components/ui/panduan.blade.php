{{-- Panduan singkat halaman (dari App\Support\Panduan). Bisa disembunyikan; pilihan diingat per halaman di perangkat. --}}
@props(['panduan', 'kunci'])
<div x-data="{ buka: true, istilah: window.innerWidth >= 640 }" x-init="buka = localStorage.getItem('panduan:{{ $kunci }}') !== 'tutup'">
    <section x-show="buka" class="rounded-2xl border border-sky-200 bg-sky-50/70 p-4 sm:p-5" aria-label="Panduan singkat">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white sm:h-10 sm:w-10"><x-heroicon-o-light-bulb class="h-5 w-5" /></span>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-wide text-sky-700 sm:text-[11px]">Panduan singkat</p>
                <h2 class="truncate text-sm font-extrabold text-zinc-900 sm:text-base">{{ $panduan['judul'] }}</h2>
            </div>
            <button type="button" @click="buka = false; localStorage.setItem('panduan:{{ $kunci }}', 'tutup')"
                class="flex h-9 shrink-0 items-center gap-1 rounded-lg px-2 text-xs font-bold text-sky-700 hover:bg-sky-100" aria-label="Sembunyikan panduan">
                <x-heroicon-m-x-mark class="h-4 w-4" /><span class="hidden sm:inline">Sembunyikan</span>
            </button>
        </div>
        <div class="mt-3 sm:pl-[3.25rem]">
            <p class="text-sm leading-6 text-zinc-700">{{ $panduan['isi'] }}</p>
            @if (! empty($panduan['langkah']))
            <ol class="mt-2 space-y-1">
                @foreach ($panduan['langkah'] as $langkah)
                <li class="flex gap-2 text-sm leading-6 text-zinc-700">
                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-sky-600 text-[11px] font-bold text-white">{{ $loop->iteration }}</span>{{ $langkah }}
                </li>
                @endforeach
            </ol>
            @endif
            @if (! empty($panduan['istilah']))
            <button type="button" @click="istilah = !istilah" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-sky-700 sm:hidden">
                <x-heroicon-m-chevron-right class="h-4 w-4 transition" ::class="istilah && 'rotate-90'" /> Arti istilah ({{ count($panduan['istilah']) }})
            </button>
            <dl x-show="istilah" class="mt-2 grid gap-2 sm:mt-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($panduan['istilah'] as $nama => $arti)
                <div class="rounded-xl bg-white/80 px-3 py-2 ring-1 ring-sky-100">
                    <dt class="text-xs font-extrabold text-sky-800">{{ $nama }}</dt>
                    <dd class="mt-0.5 text-xs leading-5 text-zinc-600">{{ $arti }}</dd>
                </div>
                @endforeach
            </dl>
            @endif
        </div>
    </section>
    <button type="button" x-show="!buka" x-cloak @click="buka = true; localStorage.removeItem('panduan:{{ $kunci }}')"
        class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-sky-50 px-3 text-xs font-bold text-sky-700 ring-1 ring-sky-200 hover:bg-sky-100">
        <x-heroicon-o-light-bulb class="h-4 w-4" /> Tampilkan panduan halaman ini
    </button>
</div>
