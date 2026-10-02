<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Aturan sama dengan StoreAsetRequest; unique (kode_barang, nup) mengabaikan aset yang diubah.
 */
class UpdateAsetRequest extends StoreAsetRequest {}
