<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('status')->default('draft');
            $table->json('snapshot_kriteria')->nullable();
            $table->json('snapshot_ambang')->nullable();
            $table->timestamp('dihitung_pada')->nullable();
            $table->foreignId('dihitung_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('difinalisasi_pada')->nullable();
            $table->foreignId('difinalisasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('alasan_buka_kembali')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_penilaian');
    }
};
