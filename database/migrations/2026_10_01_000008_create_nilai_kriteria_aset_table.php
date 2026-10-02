<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_kriteria_aset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->foreignId('kriteria_id')->constrained('kriteria')->cascadeOnDelete();
            $table->decimal('nilai', 8, 2);
            $table->timestamps();

            $table->unique(['periode_id', 'aset_id', 'kriteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_kriteria_aset');
    }
};
