<?php

use App\Enums\FaturaDurumu;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Support\TurkishFormat;

it('Türkçe para biçimini çözer', function (mixed $input, ?string $expected) {
    expect(TurkishFormat::money($input))->toBe($expected);
})->with([
    ['1.250,50', '1250.50'],
    ['1250,5', '1250.50'],
    ['1250.5', '1250.50'],
    ['3.000', '3000.00'],
    ['1.250.000', '1250000.00'],
    ['12.500,00 TL', '12500.00'],
    ['₺ 48.000', '48000.00'],
    [1250.5, '1250.50'],
    [42, '42.00'],
    ['bin lira', null],
    ['', null],
    [null, null],
]);

it('tarih biçimlerini çözer', function (mixed $input, ?string $expected) {
    expect(TurkishFormat::date($input))->toBe($expected);
})->with([
    ['15.03.2025', '2025-03-15'],
    ['15/03/2025', '2025-03-15'],
    ['2025-03-15', '2025-03-15'],
    [45731, '2025-03-15'],
    ['31.02.2025', null],
    ['geçen hafta', null],
]);

it('evet/hayır değerlerini çözer', function (mixed $input, ?bool $expected) {
    expect(TurkishFormat::bool($input))->toBe($expected);
})->with([
    ['Evet', true], ['1', true], ['true', true], ['Onaylı', true],
    ['Hayır', false], ['0', false], ['false', false],
    ['belki', null],
]);

it('telefonu biçimler ve maskeler', function () {
    expect(TurkishFormat::phone('05321234567'))->toBe('0532 123 45 67')
        ->and(TurkishFormat::phone('+90 532 123 45 67'))->toBe('0532 123 45 67')
        ->and(TurkishFormat::maskPhone('0532 123 45 67'))->toBe('0532 *** ** 67')
        ->and(TurkishFormat::maskPhone('5321234567'))->toBe('0532 *** ** 67')
        ->and(TurkishFormat::maskPhone('12'))->toBe('**');
});

it('eski seçenek metinlerini enum değerine çevirir', function () {
    expect(OdemeDurumu::fromLabel('ÖDENMEMİŞ'))->toBe(OdemeDurumu::Odenmemis)
        ->and(OdemeDurumu::fromLabel('KISMİ ÖDENMİŞ'))->toBe(OdemeDurumu::KismiOdenmis)
        ->and(OdemeDurumu::fromLabel('Ödendi'))->toBe(OdemeDurumu::Odenmis)
        ->and(TehlikeSinifi::fromLabel('Çok Tehlikeli'))->toBe(TehlikeSinifi::CokTehlikeli)
        ->and(TehlikeSinifi::fromLabel('Az Tehlikeli'))->toBe(TehlikeSinifi::AzTehlikeli)
        ->and(FaturaDurumu::fromLabel('TESLİM EDİLECEK'))->toBe(FaturaDurumu::TeslimEdilecek)
        ->and(FaturaDurumu::fromLabel('İPTAL'))->toBe(FaturaDurumu::Iptal)
        ->and(OdemeDurumu::fromLabel('belki'))->toBeNull();
});

it('tutarı tek biçimde gösterir: sonda bölünmez boşluk ve ₺', function () {
    expect(TurkishFormat::tl(213800))->toBe("213.800\u{00A0}₺")
        ->and(TurkishFormat::tl('37833.21', 2))->toBe("37.833,21\u{00A0}₺")
        ->and(TurkishFormat::tl(null))->toBe("0\u{00A0}₺");
});
