<?php

namespace App\Enums;

use App\Enums\Concerns\LabelLookup;
use Filament\Support\Contracts\HasLabel;

/**
 * Eski ekleme ekranı AYLIK/YILLIK/HAFTALIK, düzenleme ekranı W30/W45/HAZIRLANACAK
 * listesini kullanıyordu. Veri kaybı olmasın diye iki liste birleştirildi.
 * Hangi listenin doğru olduğu iş sahibine sorulacak (README: açık sorular).
 */
enum FaturaTipi: string implements HasLabel
{
    use LabelLookup;

    case Aylik = 'aylik';
    case Yillik = 'yillik';
    case Haftalik = 'haftalik';
    case W30 = 'w30';
    case W45 = 'w45';
    case Hazirlanacak = 'hazirlanacak';

    public function getLabel(): string
    {
        return match ($this) {
            self::Aylik => 'Aylık',
            self::Yillik => 'Yıllık',
            self::Haftalik => 'Haftalık',
            self::W30 => 'W30',
            self::W45 => 'W45',
            self::Hazirlanacak => 'Hazırlanacak',
        };
    }
}
