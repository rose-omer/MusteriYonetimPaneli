<?php

namespace App\Filament\Resources\Faaliyets;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Faaliyets\Pages\ManageFaaliyets;
use App\Models\Faaliyet;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class FaaliyetResource extends Resource
{
    protected static ?string $model = Faaliyet::class;

    protected static ?string $slug = 'faaliyetler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Yönetim';

    protected static ?string $recordTitleAttribute = 'isim';

    protected static ?string $modelLabel = 'faaliyet';

    protected static ?string $pluralModelLabel = 'faaliyetler';

    protected static ?string $navigationLabel = 'Faaliyetler';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('isim')
                    ->label('Faaliyet adı')
                    ->required()
                    ->minLength(2)
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('isim')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('customers'))
            ->defaultSort('isim')
            ->paginated([25, 50, 100])
            ->columns([
                // Yeniden adlandırma yabancı anahtarı değiştirmez; bağlı müşteriler korunur.
                TextInputColumn::make('isim')
                    ->label('Faaliyet')
                    ->rules(['required', 'string', 'min:2', 'max:255'])
                    ->searchable()
                    ->sortable()
                    ->disabled(fn () => ! Gate::allows('create', Faaliyet::class)),
                TextColumn::make('customers_count')
                    ->label('Müşteri')
                    ->numeric(locale: 'tr')
                    ->alignEnd()
                    ->sortable()
                    ->url(fn (Faaliyet $record) => CustomerResource::getUrl('index', ['filters' => ['faaliyet' => ['values' => [$record->id]]]])),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->label('Yeniden adlandır'),
                DeleteAction::make()
                    ->iconButton()
                    ->label('Sil')
                    ->modalHeading(fn (Faaliyet $record) => "“{$record->isim}” silinsin mi?")
                    ->modalDescription(function (Faaliyet $record): string {
                        $count = $record->customers_count ?? $record->customers()->count();

                        return $count > 0
                            ? "Bu faaliyete bağlı {$count} müşteri var. Silinirse bu müşterilerin faaliyet alanı boşalır."
                            : 'Bu faaliyete bağlı müşteri yok.';
                    }),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading('Henüz faaliyet yok')
            ->emptyStateDescription('Müşterileri sektöre göre gruplamak için faaliyet ekleyin.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaaliyets::route('/'),
        ];
    }
}
