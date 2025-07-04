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
        Schema::table('productos_comprobante', function (Blueprint $table) {
            $table->string('dni_ce', 9)->nullable()->after('nombres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos_comprobante', function (Blueprint $table) {
            $table->dropColumn('dni_ce');
        });
    }
};
