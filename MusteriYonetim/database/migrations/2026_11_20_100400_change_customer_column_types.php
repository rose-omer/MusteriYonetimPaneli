<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daralt adımı: tipler düzelir, eski "faaliyet" metni kalkar, firma_no tekil olur.
 * Önceki veri göçü değerleri bu tiplere uygun hâle getirdi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('hizmet_alan_isyeri_unvani')->nullable(false)->change();
            $table->boolean('sozlesme_onay_durumu')->default(false)->change();
            $table->date('firma_ziyareti')->nullable()->change();
            $table->unique('firma_no');
            $table->index('odeme_durumu');
            $table->index('il');
            $table->softDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('faaliyet');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('faaliyet')->nullable()->after('gorunur_unvani');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['il']);
            $table->dropIndex(['odeme_durumu']);
            $table->dropUnique(['firma_no']);
            $table->string('firma_ziyareti')->nullable()->change();
            $table->string('sozlesme_onay_durumu')->nullable()->change();
            $table->string('hizmet_alan_isyeri_unvani')->nullable()->change();
        });
    }
};
