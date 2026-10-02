<x-layouts.app title="Data Pengguna">
    <x-ui.page-header title="Data Pengguna" subtitle="Kelola akun pengguna sistem">
        <x-slot:actions>
            <a href="#" class="btn btn-primary btn-sm gap-1">
                <x-heroicon-o-plus class="w-4 h-4" /> Tambah Pengguna
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.empty-state icon="users" title="Belum ada pengguna" text="Fitur ini akan tersedia setelah implementasi Fase 2." />
    </x-ui.card>
</x-layouts.app>
