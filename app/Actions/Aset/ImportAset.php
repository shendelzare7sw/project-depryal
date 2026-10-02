<?php

declare(strict_types=1);

namespace App\Actions\Aset;

use App\Enums\StatusAset;
use App\Enums\UserRole;
use App\Imports\AsetImportResult;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\User;
use App\Notifications\SistemNotification;
use App\Services\Notifikasi;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportAset
{
    /** @var array<string, int> nama kategori (huruf kecil) => id */
    private array $kategoriIds = [];

    public function __construct(private readonly Notifikasi $notifikasi) {}

    /**
     * Simpan baris valid: upsert berdasarkan (kode_barang, nup), kategori dibuat otomatis bila belum ada.
     * Aset yang pernah dihapus (soft delete) dengan kunci sama dipulihkan.
     *
     * @return int jumlah baris tersimpan
     */
    public function execute(AsetImportResult $result, ?User $oleh = null): int
    {
        if ($result->valid === []) {
            throw new DomainException('Tidak ada baris valid untuk disimpan. Perbaiki berkas lalu unggah ulang.');
        }

        $jumlah = DB::transaction(function () use ($result): int {
            foreach ($result->valid as $row) {
                $data = $row['data'];
                $aset = Aset::withTrashed()->firstOrNew(['kode_barang' => $data['kode_barang'], 'nup' => $data['nup']]);
                $aset->fill(Arr::except($data, ['kategori']));
                $aset->kategori_aset_id = $this->kategoriId((string) $data['kategori']);
                $aset->status ??= StatusAset::Aktif;
                $aset->deleted_at = null;
                $aset->save();
            }

            return count($result->valid);
        });

        $this->notifikasi->kirimKeRole([UserRole::Admin, UserRole::Operator], new SistemNotification(
            judul: 'Import data BMD selesai',
            pesan: "{$jumlah} data aset diimpor".($oleh ? " oleh {$oleh->name}" : '').'.',
            url: route('aset.index'),
            icon: 'arrow-up-tray',
            tone: 'success',
        ), $oleh);

        return $jumlah;
    }

    private function kategoriId(string $nama): int
    {
        return $this->kategoriIds[mb_strtolower($nama)] ??= (
            KategoriAset::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->orWhere('kode', $nama)->value('id')
            ?? KategoriAset::create(['kode' => $this->kodeBaru($nama), 'nama' => $nama])->id
        );
    }

    private function kodeBaru(string $nama): string
    {
        $base = Str::upper(Str::limit(Str::slug($nama), 16, '')) ?: 'KAT';
        $kode = $base;

        for ($i = 2; KategoriAset::where('kode', $kode)->exists(); $i++) {
            $kode = "{$base}-{$i}";
        }

        return $kode;
    }
}
