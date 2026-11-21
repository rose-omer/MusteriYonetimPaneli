<?php

namespace App\Filament\Resources\Faaliyets\Pages;

use App\Filament\Resources\Faaliyets\FaaliyetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFaaliyets extends ManageRecords
{
    protected static string $resource = FaaliyetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Faaliyet ekle'),
        ];
    }
}
