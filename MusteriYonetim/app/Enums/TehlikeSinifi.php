<?php

namespace App\Enums;

use App\Enums\Concerns\LabelLookup;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TehlikeSinifi: string implements HasColor, HasLabel
{
    use LabelLookup;

    case AzTehlikeli = 'az_tehlikeli';
    case Tehlikeli = 'tehlikeli';
    case CokTehlikeli = 'cok_tehlikeli';

    public function getLabel(): string
    {
        return match ($this) {
            self::AzTehlikeli => 'Az tehlikeli',
            self::Tehlikeli => 'Tehlikeli',
            self::CokTehlikeli => 'Çok tehlikeli',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::AzTehlikeli => 'success',
            self::Tehlikeli => 'warning',
            self::CokTehlikeli => 'danger',
        };
    }
}
