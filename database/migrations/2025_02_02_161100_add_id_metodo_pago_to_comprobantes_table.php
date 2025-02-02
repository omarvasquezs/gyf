<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->integer('id_metodo_pago')->nullable()->after('monto_total');
        });
    }

    public function down(): void {
        Schema::table('comprobantes', function (Blueprint $table) {
            $table->dropColumn('id_metodo_pago');
        });
    }
};
