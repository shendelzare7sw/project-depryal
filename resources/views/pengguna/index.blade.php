<x-layouts.app title="Data Pengguna" subtitle="Akun, peran, dan status akses sistem">
    @php
        $saya = auth()->user();
        $adaFilter = collect(request()->only(['q', 'role', 'status']))->filter()->isNotEmpty();
        $baru = session('password_baru');
    @endphp

    @if ($baru)
    <section x-data="{ disalin: false }" class="flex flex-col gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-400 text-amber-950"><x-heroicon-o-key class="h-5 w-5" /></span>
            <div>
                <p class="text-sm font-extrabold text-amber-950">Password baru untuk {{ '@'.$baru['username'] }}</p>
                <p class="mt-0.5 text-xs text-amber-900">Ditampilkan <strong>sekali saja</strong>. Sampaikan kepada pengguna — sistem akan memintanya mengganti kata sandi saat masuk berikutnya.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <code class="rounded-lg bg-white px-3 py-2 font-mono text-base font-bold tracking-wider text-zinc-900 ring-1 ring-amber-300">{{ $baru['password'] }}</code>
            <button type="button" @click="navigator.clipboard.writeText(@js($baru['password'])); disalin = true"
                class="inline-flex h-10 items-center gap-2 rounded-xl bg-white px-3.5 text-xs font-bold text-zinc-700 ring-1 ring-inset ring-amber-300 hover:bg-amber-100">
                <x-heroicon-o-clipboard-document class="h-4 w-4" /> <span x-text="disalin ? 'Tersalin' : 'Salin'">Salin</span>
            </button>
        </div>
    </section>
    @endif

    <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm shadow-zinc-900/[0.03]">
        <header class="border-b border-zinc-200/80 p-4 sm:p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><x-heroicon-o-users class="h-5 w-5" /></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold text-zinc-900">Daftar Pengguna</h2>
                        <p class="mt-0.5 text-xs text-zinc-500">{{ $users->total() }} akun {{ $adaFilter ? 'sesuai filter' : 'terdaftar' }}</p>
                    </div>
                </div>
                <x-ui.btn tone="primary" icon="user-plus" :href="route('pengguna.create')">Tambah Pengguna</x-ui.btn>
            </div>
            <form method="GET" class="mt-4 grid grid-cols-2 gap-2 xl:grid-cols-[minmax(220px,1fr)_minmax(170px,0.35fr)_minmax(150px,0.3fr)_auto]">
                <label class="relative col-span-2 xl:col-span-1">
                    <span class="sr-only">Cari pengguna</span>
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-zinc-400" />
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, username, atau email…" class="h-10 w-full rounded-xl border border-zinc-200 pl-10 pr-3 text-xs outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                </label>
                <select name="role" aria-label="Filter peran" class="h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500">
                    <option value="">Semua peran</option>
                    @foreach (\App\Enums\UserRole::cases() as $r)<option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->label() }}</option>@endforeach
                </select>
                <select name="status" aria-label="Filter status" class="h-10 w-full rounded-xl border border-zinc-200 bg-white px-3 text-xs text-zinc-700 outline-none focus:border-brand-500">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
                <div class="col-span-2 flex gap-2 xl:col-span-1">
                    <x-ui.btn type="submit" tone="dark" icon="funnel" class="flex-1">Filter</x-ui.btn>
                    @if ($adaFilter)<x-ui.btn tone="soft-rose" icon="x-mark" :href="route('pengguna.index')" aria-label="Hapus filter"></x-ui.btn>@endif
                </div>
            </form>
        </header>

        <div class="divide-y divide-zinc-100 lg:hidden">
            @forelse ($users as $u)
            <article id="m-{{ $loop->iteration }}" class="scroll-mt-24 p-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-sm font-bold text-white">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="truncate text-sm font-extrabold text-zinc-900">{{ $u->name }}@if ($u->is($saya)) <span class="text-xs font-semibold text-brand-600">(Anda)</span>@endif</h3>
                            <x-ui.badge :tone="$u->is_active ? 'success' : 'ghost'" dot>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                        </div>
                        <p class="mt-0.5 truncate text-[11px] text-zinc-500">{{ '@'.$u->username }}{{ $u->email ? ' · '.$u->email : '' }}</p>
                        <x-ui.badge :tone="$u->role->color()" class="mt-1.5">{{ $u->role->label() }}</x-ui.badge>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2">@include('pengguna._actions', ['mobile' => true])</div>
            </article>
            @empty
            <x-ui.empty-state icon="users" title="Tidak ada pengguna" text="Ubah filter pencarian." />
            @endforelse
        </div>

        @if ($users->isNotEmpty())
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[900px] table-fixed text-left text-sm">
                <colgroup><col><col class="w-[22%]"><col class="w-52"><col class="w-28"><col class="w-36"></colgroup>
                <thead class="bg-zinc-50 text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                    <tr><th class="px-3 py-3">Pengguna</th><th class="px-3 py-3">Email / NIP</th><th class="px-3 py-3">Peran</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($users as $u)
                    <tr id="d-{{ $loop->iteration }}" class="scroll-mt-24 hover:bg-zinc-50/80">
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-600 to-brand-800 text-xs font-bold text-white">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                                <div class="min-w-0"><p class="truncate font-bold text-zinc-800">{{ $u->name }}@if ($u->is($saya)) <span class="text-xs font-semibold text-brand-600">(Anda)</span>@endif</p><p class="truncate text-[11px] text-zinc-500">{{ '@'.$u->username }}{{ $u->jabatan ? ' · '.$u->jabatan : '' }}</p></div>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-xs text-zinc-600"><p class="truncate">{{ $u->email ?? '—' }}</p><p class="truncate text-zinc-400">{{ $u->nip ?? '' }}</p></td>
                        <td class="px-3 py-3"><x-ui.badge :tone="$u->role->color()">{{ $u->role->label() }}</x-ui.badge></td>
                        <td class="px-3 py-3"><x-ui.badge :tone="$u->is_active ? 'success' : 'ghost'" dot>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge></td>
                        <td class="px-3 py-3"><div class="flex justify-end gap-1.5">@include('pengguna._actions', ['mobile' => false])</div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <x-ui.muat-lagi :items="$users" />
    </section>
</x-layouts.app>
