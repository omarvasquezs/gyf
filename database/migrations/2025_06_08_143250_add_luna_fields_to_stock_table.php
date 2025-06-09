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
        Schema::table('stock', function (Blueprint $table) {
            // Add new fields for Lunas
            $table->foreignId('tipo_luna_id')->nullable()->constrained('tipo_lunas')->onDelete('set null');
            $table->foreignId('diseno_luna_id')->nullable()->constrained('diseno_lunas')->onDelete('set null');
            $table->foreignId('laboratorio_luna_id')->nullable()->constrained('laboratorios_lunas')->onDelete('set null');
            $table->decimal('indice_luna', 8, 2)->nullable();

            // Make existing fields nullable
            $table->foreignId('id_marca')->nullable()->change();
            $table->integer('num_stock')->nullable()->change();
            $table->string('genero')->nullable()->change();
            $table->foreignId('id_material')->nullable()->change();
            $table->date('fecha_compra')->nullable()->change();
            $table->string('imagen')->nullable()->change();
            $table->string('codigo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            // Drop foreign keys and columns for Lunas
            $table->dropForeign(['tipo_luna_id']);
            $table->dropColumn('tipo_luna_id');
            $table->dropForeign(['diseno_luna_id']);
            $table->dropColumn('diseno_luna_id');
            $table->dropForeign(['laboratorio_luna_id']);
            $table->dropColumn('laboratorio_luna_id');
            $table->dropColumn('indice_luna');

            // Revert changes to existing fields - NOTE: This assumes original state was not nullable.
            // If any of these were nullable before, this down migration will need adjustment.
            $table->foreignId('id_marca')->nullable(false)->change();
            $table->integer('num_stock')->nullable(false)->change();
            $table->string('genero')->nullable(false)->change();
            $table->foreignId('id_material')->nullable(false)->change();
            $table->date('fecha_compra')->nullable(false)->change();
            $table->string('imagen')->nullable(false)->change();
            $table->string('codigo')->nullable(false)->change();
        });
    }
};
