<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Genişlet adımı: yabancı anahtar sütunu eklenir, eski "faaliyet" metni şimdilik kalır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('faaliyet_id')->nullable()->after('gorunur_unvani')
                ->constrained('faaliyets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faaliyet_id');
        });
    }
};
