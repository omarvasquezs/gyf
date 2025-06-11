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
        Schema::create('comprobante_config', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('G & F oftalmólogas. S.A.C.');
            $table->string('address')->default('Calle almenara 124 interior 201 surquillo');
            $table->string('ruc')->default('20613814265');
            $table->string('phone')->default('940 213 168');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('font_family')->default('Courier New');
            $table->integer('font_size')->default(8);
            $table->string('header_alignment')->default('center');
            $table->boolean('show_logo')->default(false);
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_config');
    }
};
