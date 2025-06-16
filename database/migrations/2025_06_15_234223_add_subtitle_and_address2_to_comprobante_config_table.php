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
        Schema::table('comprobante_config', function (Blueprint $table) {
            $table->string('sub_title')->nullable()->after('company_name');
            $table->string('address_2')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comprobante_config', function (Blueprint $table) {
            $table->dropColumn(['sub_title', 'address_2']);
        });
    }
};
