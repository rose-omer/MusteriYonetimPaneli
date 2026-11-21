<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Pages\ImportCustomers;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('iceAktar')
                ->label('İçe aktar')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->color('gray')
                ->url(fn () => ImportCustomers::getUrl())
                ->visible(fn () => Gate::allows('import', Customer::class)),
            CreateAction::make()->label('Müşteri ekle'),
        ];
    }
}
