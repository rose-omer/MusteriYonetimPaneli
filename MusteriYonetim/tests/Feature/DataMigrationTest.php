<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Eski şemadaki serbest metin verisinin yeni tiplere çevrildiğini doğrular.
 */
it('eski kayıtları enum, boolean, tarih ve faaliyet_id değerlerine çevirir', function () {
    // Bu dosyadaki 7 yeni göçü geri al: şema 2025 hâline döner.
    $this->artisan('migrate:rollback', ['--step' => 7])->assertSuccessful();
    expect(Schema::hasColumn('customers', 'faaliyet'))->toBeTrue();

    DB::table('users')->insert(['name' => 'Eski', 'email' => 'eski@demo.test', 'password' => 'x', 'role' => 'guest']);
    DB::table('customers')->insert([
        ['id' => 1, 'hizmet_alan_isyeri_unvani' => 'Bir A.Ş.', 'firma_no' => '1', 'faaliyet' => 'Tekstil', 'tehlike_sinifi' => 'Çok Tehlikeli',
            'fatura_tipi' => 'W30', 'fatura_durumu' => 'TESLİM EDİLDİ', 'odeme_durumu' => 'KISMİ ÖDENMİŞ', 'sozlesme_onay_durumu' => 'evet', 'firma_ziyareti' => '15.03.2025'],
        ['id' => 2, 'hizmet_alan_isyeri_unvani' => null, 'firma_no' => '1', 'faaliyet' => 'Tekstil', 'tehlike_sinifi' => 'bilinmiyor',
            'fatura_tipi' => 'AYLIK', 'fatura_durumu' => 'KESİLDİ', 'odeme_durumu' => 'ÖDENMEMİŞ', 'sozlesme_onay_durumu' => 'false', 'firma_ziyareti' => 'geçen hafta'],
    ]);

    $this->artisan('migrate')->assertSuccessful();

    $rows = DB::table('customers')->orderBy('id')->get();
    $tekstil = DB::table('faaliyets')->where('isim', 'Tekstil')->value('id');

    expect(Schema::hasColumn('customers', 'faaliyet'))->toBeFalse()
        ->and(DB::table('users')->where('email', 'eski@demo.test')->value('role'))->toBe('viewer')
        ->and($rows[0]->faaliyet_id)->toBe($tekstil)
        ->and($rows[0]->tehlike_sinifi)->toBe('cok_tehlikeli')
        ->and($rows[0]->fatura_tipi)->toBe('w30')
        ->and($rows[0]->fatura_durumu)->toBe('teslim_edildi')
        ->and($rows[0]->odeme_durumu)->toBe('kismi_odenmis')
        ->and((bool) $rows[0]->sozlesme_onay_durumu)->toBeTrue()
        ->and($rows[0]->firma_ziyareti)->toStartWith('2025-03-15')
        ->and($rows[1]->hizmet_alan_isyeri_unvani)->toBe('Adsız firma #2')
        ->and($rows[1]->firma_no)->toBeNull()
        ->and($rows[1]->tehlike_sinifi)->toBeNull()
        ->and($rows[1]->fatura_tipi)->toBe('aylik')
        ->and($rows[1]->odeme_durumu)->toBe('odenmemis')
        ->and((bool) $rows[1]->sozlesme_onay_durumu)->toBeFalse()
        ->and($rows[1]->firma_ziyareti)->toBeNull();
});
