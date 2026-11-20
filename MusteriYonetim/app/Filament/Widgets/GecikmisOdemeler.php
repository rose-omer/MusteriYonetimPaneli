<?php

namespace App\Filament\Widgets;

use App\Enums\OdemeDurumu;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/** Ödemesi açık ilk 10 firma, tutara göre azalan. */
class GecikmisOdemeler extends TableWidget
{
    protected static ?int $sort = 4;

    // Sorgular hafif; iskelet yanıp sönmesin diye hemen yüklenir.
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $acik = array_map(fn (OdemeDurumu $d) => $d->value, OdemeDurumu::acik());

        return $table
            ->heading('Ödemesi gecikmiş firmalar')
            ->description('Ödenmedi ya da kısmi ödendi, tutara göre ilk 10')
            ->query(fn () => Customer::query()->whereIn('odeme_durumu', $acik)->orderByDesc('tutar')->limit(10))
            ->paginated(false)
            ->recordUrl(fn (Customer $record) => CustomerResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('hizmet_alan_isyeri_unvani')
                    ->label('Unvan')
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),
                TextColumn::make('il')->label('İl')->visibleFrom('md'),
                TextColumn::make('odeme_durumu')->label('Ödeme')->badge(),
                TextColumn::make('tutar')
                    ->label('Tutar')
                    ->money('TRY', locale: 'tr')
                    ->alignEnd()
                    ->extraAttributes(['class' => 'tabular-nums']),
            ])
            ->headerActions([
                Action::make('tumu')
                    ->label('Tümünü gör')
                    ->link()
                    ->url(CustomerResource::getUrl('index', ['filters' => ['odeme_durumu' => ['values' => $acik]], 'sort' => 'tutar:desc'])),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->emptyStateHeading('Gecikmiş ödeme yok')
            ->emptyStateDescription('Bütün firmaların ödemesi tamamlanmış.');
    }
}
