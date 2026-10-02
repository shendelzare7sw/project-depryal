<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_moora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_penilaian')->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('aset')->cascadeOnDelete();
            $table->decimal('yi', 12, 6);
            $table->decimal('skor_relatif', 6, 2);
            $table->unsignedInteger('ranking');
            $table->string('rekomendasi');
            $table->json('detail');
            $table->timestamps();

            $table->unique(['periode_id', 'aset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_moora');
    }
};
