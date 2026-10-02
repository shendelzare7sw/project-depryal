<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_aset_id')->constrained('kategori_aset')->restrictOnDelete();
            $table->string('kode_barang');
            $table->unsignedInteger('nup')->default(1);
            $table->string('nama_barang');
            $table->unsignedInteger('jumlah')->default(1);
            $table->decimal('luas', 12, 2)->nullable();
            $table->date('tanggal_perolehan');
            $table->decimal('harga_satuan', 18, 2)->default(0);
            $table->decimal('nilai_perolehan', 18, 2)->default(0);
            $table->unsignedInteger('umur_ekonomis')->default(0);
            $table->decimal('akumulasi_penyusutan', 18, 2)->default(0);
            $table->integer('sisa_ueb')->default(0);
            $table->decimal('nilai_buku', 18, 2)->default(0);
            $table->string('lokasi')->nullable();
            $table->string('status')->default('aktif');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['kode_barang', 'nup']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
