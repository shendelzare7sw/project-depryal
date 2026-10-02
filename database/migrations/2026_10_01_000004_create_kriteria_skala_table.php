<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria_skala', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_id')->constrained('kriteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai');
            $table->string('label');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['kriteria_id', 'nilai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria_skala');
    }
};
