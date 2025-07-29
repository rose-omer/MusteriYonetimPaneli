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
        Schema::table('customers', function (Blueprint $table) {
            // Sütunu nullable yap
            $table->string('hizmet_alan_isyeri_unvani')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Değişikliği geri al (nullable olmayan eski haline döndür)
            $table->string('hizmet_alan_isyeri_unvani')->nullable(false)->change();
        });
    }
};