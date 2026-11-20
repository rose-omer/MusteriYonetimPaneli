<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Yönetici',
            self::Editor => 'Düzenleyici',
            self::Viewer => 'İzleyici',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'primary',
            self::Editor => 'vurgu',
            self::Viewer => 'gray',
        };
    }

    /** Müşteri kaydı ekleyip düzenleyebilir mi? */
    public function canWrite(): bool
    {
        return $this === self::Admin || $this === self::Editor;
    }

    /** Telefon numarasını maskesiz görebilir mi? */
    public function canSeeFullPhone(): bool
    {
        return $this->canWrite();
    }
}
