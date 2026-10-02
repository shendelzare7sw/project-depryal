<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->text('deskripsi_kondisi')->nullable();
            $table->timestamps();

            $table->unique(['periode_id', 'aset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_aset');
    }
};
