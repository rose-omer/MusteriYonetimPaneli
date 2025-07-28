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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('firma_no')->nullable(); // FİRMA NO
            $table->string('hizmet_alan_isyeri_unvani'); // Hizmet Alan İşyeri Unvanı
            $table->string('gorunur_unvani')->nullable(); // GÖRÜNÜR UNVANI
            $table->string('faaliyet')->nullable(); // FAALİYET (Bu alan Excel'deki "FAALİYET" sütununa denk gelir)
            $table->integer('calisan_sayisi')->nullable(); // Hizmet Alan İşyeri Çalışan Sayısı
            $table->string('tehlike_sinifi')->nullable(); // Hizmet Alan İşyeri Tehlike Sınıfı (Bu alan Excel'deki "Tehlike Sınıfı"na denk gelir)
            $table->string('firma_sorumlusu')->nullable(); // FİRMA SORUMLUSU
            $table->string('telefon_numarasi')->nullable(); // YETKİLİ KİŞİ TELEFON NUMARASI
            $table->string('il')->nullable(); // Hizmet Alan İşyeri İli
            $table->string('mahalle')->nullable(); // MAHALLE
            $table->string('konum_url')->nullable(); // KONUM (Harita linki olabilir)
            $table->string('fatura_tipi')->nullable(); // FATURA TİPİ
            $table->string('fatura_durumu')->nullable(); // FATURA DURUMU
            $table->decimal('tutar', 10, 2)->nullable(); // TUTAR
            $table->string('odeme_durumu')->nullable(); // ÖDEME
            $table->string('sozlesme_onay_durumu')->nullable(); // Sözleşme Onay Durumu
            $table->string('igu')->nullable(); // İGU
            $table->string('ih')->nullable(); // İH
            $table->string('firma_ziyareti')->nullable(); // FİRMA ZİYARETİ
            $table->string('defter')->nullable(); // DEFTER
            $table->string('ra')->nullable(); // RA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};