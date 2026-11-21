<?php

namespace App\Enums;

use App\Enums\Concerns\LabelLookup;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OdemeDurumu: string implements HasColor, HasLabel
{
    use LabelLookup;

    case Odenmis = 'odenmis';
    case KismiOdenmis = 'kismi_odenmis';
    case Odenmemis = 'odenmemis';

    public function getLabel(): string
    {
        return match ($this) {
            self::Odenmis => 'Ödendi',
            self::KismiOdenmis => 'Kısmi ödendi',
            self::Odenmemis => 'Ödenmedi',
        };
    }

    /** @return list<string> */
    public function aliases(): array
    {
        return match ($this) {
            self::Odenmis => ['Ödenmiş', 'Ödendi', 'Odendi'],
            self::KismiOdenmis => ['Kısmi ödenmiş', 'Kısmi', 'Kismi'],
            self::Odenmemis => ['Ödenmemiş', 'Ödenmedi'],
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Odenmis => 'gray',
            self::KismiOdenmis => 'primary',
            self::Odenmemis => 'vurgu',
        };
    }

    /** @return list<self> */
    public static function acik(): array
    {
        return [self::Odenmemis, self::KismiOdenmis];
    }
}
