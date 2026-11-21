<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Veri göçü: eski rol metinlerini UserRole enum değerlerine çevirir.
 * guest, observe ve tanımsız roller en dar yetkiye (viewer) iner.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotIn('role', ['admin', 'editor', 'viewer'])
            ->orWhereNull('role')
            ->update(['role' => 'viewer']);
    }

    public function down(): void
    {
        // Geri dönüşte eski "guest" değeri kullanılır; admin ve editor korunur.
        DB::table('users')->where('role', 'viewer')->update(['role' => 'guest']);
    }
};
