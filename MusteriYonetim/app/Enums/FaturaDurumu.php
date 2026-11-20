<?php

namespace App\Enums;

use App\Enums\Concerns\LabelLookup;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/** FaturaTipi ile aynı nedenle iki eski liste birleştirildi. */
enum FaturaDurumu: string implements HasColor, HasLabel
{
    use LabelLookup;

    case Kesildi = 'kesildi';
    case Kesilmedi = 'kesilmedi';
    case TeslimEdilecek = 'teslim_edilecek';
    case TeslimEdildi = 'teslim_edildi';
    case Iptal = 'iptal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Kesildi => 'Kesildi',
            self::Kesilmedi => 'Kesilmedi',
            self::TeslimEdilecek => 'Teslim edilecek',
            self::TeslimEdildi => 'Teslim edildi',
            self::Iptal => 'İptal',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Kesildi, self::TeslimEdildi => 'success',
            self::Kesilmedi, self::TeslimEdilecek => 'warning',
            self::Iptal => 'gray',
        };
    }
}
