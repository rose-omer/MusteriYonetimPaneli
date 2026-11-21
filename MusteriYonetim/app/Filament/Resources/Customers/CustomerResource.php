<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Schemas\CustomerInfolist;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use App\Models\User;
use App\Support\TurkishFormat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::BuildingOffice2;

    protected static ?string $recordTitleAttribute = 'hizmet_alan_isyeri_unvani';

    protected static ?string $modelLabel = 'müşteri';

    protected static ?string $pluralModelLabel = 'müşteriler';

    protected static ?string $navigationLabel = 'Müşteriler';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /** @return list<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['hizmet_alan_isyeri_unvani', 'gorunur_unvani', 'firma_no'];
    }

    /** @return array<string, string> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Customer $record */
        return array_filter([
            'Firma no' => $record->firma_no,
            'İl' => $record->il,
        ]);
    }

    /**
     * KVKK: izleyici rolü telefonu maskeli görür (0532 *** ** 67).
     */
    public static function phoneForCurrentUser(?string $phone): ?string
    {
        return self::currentUserSeesFullPhone()
            ? TurkishFormat::phone($phone)
            : TurkishFormat::maskPhone($phone);
    }

    /** KVKK: telefonu tam göremeyen kullanıcı telefonla da arayamaz (arama ile numara tahmini engellenir). */
    public static function currentUserSeesFullPhone(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->role->canSeeFullPhone();
    }
}
