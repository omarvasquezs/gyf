<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('laboratorios_luna')) {
            Schema::rename('laboratorios_luna', 'laboratorios_lunas');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('laboratorios_lunas')) {
            Schema::rename('laboratorios_lunas', 'laboratorios_luna');
        }
    }
};
