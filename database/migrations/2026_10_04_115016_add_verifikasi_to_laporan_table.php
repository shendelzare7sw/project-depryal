<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->string('kode_verifikasi', 32)->nullable()->unique()->after('path');
            $table->string('sha256', 64)->nullable()->after('kode_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            $table->dropUnique(['kode_verifikasi']);
            $table->dropColumn(['kode_verifikasi', 'sha256']);
        });
    }
};
