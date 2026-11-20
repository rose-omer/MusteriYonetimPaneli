<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Customer $record */
        $record = $this->getRecord();

        return $record->gorunur_unvani ?: $record->hizmet_alan_isyeri_unvani;
    }

    public function getSubheading(): ?string
    {
        /** @var Customer $record */
        $record = $this->getRecord();

        if ($record->trashed()) {
            return 'Bu kayıt silindi. '.$record->deleted_at?->addDays(Customer::SAKLAMA_GUNU)->format('d.m.Y').' tarihinde kalıcı silinecek.';
        }

        return $record->gorunur_unvani ? $record->hizmet_alan_isyeri_unvani : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Düzenle'),
            DeleteAction::make()
                ->modalDescription('Müşteri silinir. Kayıt 90 gün içinde geri alınabilir, sonra kalıcı silinir.'),
            RestoreAction::make(),
        ];
    }
}
