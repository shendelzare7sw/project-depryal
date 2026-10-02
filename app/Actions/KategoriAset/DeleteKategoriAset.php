<?php

declare(strict_types=1);

namespace App\Actions\KategoriAset;

use App\Models\KategoriAset;
use DomainException;

final class DeleteKategoriAset
{
    public function execute(KategoriAset $kategori): void
    {
        $jumlah = $kategori->aset()->withTrashed()->count();

        if ($jumlah > 0) {
            throw new DomainException("Kategori masih dipakai oleh {$jumlah} aset sehingga tidak dapat dihapus.");
        }

        $kategori->delete();
    }
}
