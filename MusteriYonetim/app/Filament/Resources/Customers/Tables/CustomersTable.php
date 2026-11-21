<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\FaturaDurumu;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Exports\CustomersExport;
use App\Filament\Pages\ImportCustomers;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Support\TurkishFormat;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('faaliyet'))
            ->defaultSort('hizmet_alan_isyeri_unvani')
            ->searchPlaceholder(fn () => CustomerResource::currentUserSeesFullPhone() ? 'Unvan, firma no ya da telefon ara' : 'Unvan ya da firma no ara')
            ->columns(self::columns())
            ->filters(self::filters())
            ->filtersFormColumns(2)
            ->filtersFormWidth('2xl')
            ->filtersTriggerAction(fn (Action $action) => $action->slideOver()->label('Filtrele'))
            ->persistFiltersInSession()
            ->persistColumnsInSession()
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                ViewAction::make()->iconButton()->label('Görüntüle'),
                EditAction::make()->iconButton()->label('Düzenle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::changeStatusAction('odeme_durumu', 'Ödeme durumunu değiştir', OdemeDurumu::class, Heroicon::OutlinedBanknotes),
                    self::changeStatusAction('fatura_durumu', 'Fatura durumunu değiştir', FaturaDurumu::class, Heroicon::OutlinedDocumentText),
                    BulkAction::make('excelAktar')
                        ->label("Seçilenleri Excel'e aktar")
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->visible(fn () => Gate::allows('export', Customer::class))
                        ->action(function (Collection $records) {
                            /** @var Collection<int, Customer> $records */
                            return Excel::download(new CustomersExport($records->load('faaliyet')), 'musteriler-'.now()->format('Y-m-d').'.xlsx');
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->modalDescription('Seçilen müşteriler silinir. Silinen kayıt 90 gün içinde geri alınabilir, sonra kalıcı silinir.'),
                    RestoreBulkAction::make(),
                ])->label('Toplu işlemler'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedBuildingOffice2)
            ->emptyStateHeading(fn (Table $table) => self::isFiltered($table) ? 'Bu ölçütlere uyan müşteri yok' : 'Henüz müşteri yok')
            ->emptyStateDescription(fn (Table $table) => self::isFiltered($table)
                ? 'Aramayı ya da filtreleri değiştirin.'
                : 'Müşteriyi tek tek ekleyin ya da Excel listesini içe aktarın.')
            ->emptyStateActions([
                Action::make('filtreyiTemizle')
                    ->label('Filtreyi temizle')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedXMark)
                    ->visible(fn (Table $table) => self::isFiltered($table))
                    ->action(fn ($livewire) => $livewire->removeTableFilters()),
                CreateAction::make()
                    ->label('Müşteri ekle')
                    ->url(CustomerResource::getUrl('create'))
                    ->visible(fn (Table $table) => ! self::isFiltered($table) && Gate::allows('create', Customer::class)),
                Action::make('iceAktar')
                    ->label("Excel'den içe aktar")
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->url(fn () => ImportCustomers::getUrl())
                    ->visible(fn (Table $table) => ! self::isFiltered($table) && Gate::allows('import', Customer::class)),
            ]);
    }

    /** @return list<TextColumn> */
    private static function columns(): array
    {
        return [
            TextColumn::make('hizmet_alan_isyeri_unvani')
                ->label('Unvan')
                ->searchable(['hizmet_alan_isyeri_unvani', 'gorunur_unvani'])
                ->sortable()
                ->weight(FontWeight::SemiBold)
                ->description(fn (Customer $record) => $record->firma_no
                    ? new HtmlString('No <span class="mtp-num">'.e($record->firma_no).'</span>')
                    : null)
                ->wrap()
                ->extraHeaderAttributes(['class' => 'md:min-w-52 2xl:min-w-64']),
            TextColumn::make('firma_no')
                ->label('Firma no')
                ->searchable()
                ->sortable()
                ->fontFamily(FontFamily::Mono)
                ->extraAttributes(['class' => 'tabular-nums'])
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('faaliyet.isim')
                ->label('Faaliyet')
                ->sortable()
                ->toggleable()
                ->wrap()
                ->visibleFrom('lg'),
            TextColumn::make('il')
                ->label('İl')
                ->sortable()
                ->toggleable(),
            TextColumn::make('tehlike_sinifi')
                ->label('Tehlike sınıfı')
                ->badge()
                ->sortable()
                ->toggleable()
                ->visibleFrom('md'),
            TextColumn::make('telefon_numarasi')
                ->label('Telefon')
                ->searchable(fn () => CustomerResource::currentUserSeesFullPhone())
                ->formatStateUsing(fn (?string $state) => CustomerResource::phoneForCurrentUser($state))
                ->fontFamily(FontFamily::Mono)
                ->extraAttributes(['class' => 'tabular-nums whitespace-nowrap'])
                ->toggleable()
                ->visibleFrom('lg'),
            TextColumn::make('tutar')
                ->label('Tutar')
                ->formatStateUsing(fn ($state) => TurkishFormat::tl($state, 2))
                ->alignEnd()
                ->sortable()
                ->fontFamily(FontFamily::Mono)
                ->extraAttributes(['class' => 'tabular-nums whitespace-nowrap'])
                ->summarize(Sum::make()->label('Toplam')->formatStateUsing(fn ($state) => TurkishFormat::tl($state, 2)))
                ->toggleable()
                ->visibleFrom('md'),
            TextColumn::make('odeme_durumu')
                ->label('Ödeme')
                ->badge()
                ->sortable(),
            TextColumn::make('fatura_durumu')
                ->label('Fatura')
                ->badge()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('sozlesme_onay_durumu')
                ->label('Sözleşme')
                ->badge()
                ->formatStateUsing(fn (bool $state) => $state ? 'Onaylı' : 'Onaysız')
                ->color(fn (bool $state) => $state ? 'gray' : 'vurgu')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('firma_ziyareti')
                ->label('Son ziyaret')
                ->date('d.m.Y')
                ->sortable()
                ->fontFamily(FontFamily::Mono)
                ->extraAttributes(['class' => 'tabular-nums'])
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->label('Eklenme')
                ->date('d.m.Y')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /** @return list<BaseFilter> */
    private static function filters(): array
    {
        return [
            SelectFilter::make('il')
                ->label('İl')
                ->multiple()
                ->searchable()
                ->options(fn () => Customer::query()->whereNotNull('il')->distinct()->orderBy('il')->pluck('il', 'il')->all()),
            SelectFilter::make('faaliyet')
                ->label('Faaliyet')
                ->relationship('faaliyet', 'isim')
                ->multiple()
                ->searchable()
                ->preload(),
            SelectFilter::make('tehlike_sinifi')
                ->label('Tehlike sınıfı')
                ->multiple()
                ->options(TehlikeSinifi::class),
            SelectFilter::make('fatura_durumu')
                ->label('Fatura durumu')
                ->multiple()
                ->options(FaturaDurumu::class),
            SelectFilter::make('odeme_durumu')
                ->label('Ödeme durumu')
                ->multiple()
                ->options(OdemeDurumu::class),
            TernaryFilter::make('sozlesme_onay_durumu')
                ->label('Sözleşme onayı')
                ->trueLabel('Onaylı')
                ->falseLabel('Onaysız')
                ->placeholder('Tümü'),
            Filter::make('tutar')
                ->label('Tutar aralığı')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('min')->label('En az (₺)')->numeric()->minValue(0),
                        TextInput::make('max')->label('En çok (₺)')->numeric()->minValue(0),
                    ]),
                ])
                ->columnSpan(2)
                ->query(fn (Builder $query, array $data) => $query
                    ->when(filled($data['min'] ?? null), fn (Builder $q) => $q->where('tutar', '>=', $data['min']))
                    ->when(filled($data['max'] ?? null), fn (Builder $q) => $q->where('tutar', '<=', $data['max'])))
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if (filled($data['min'] ?? null)) {
                        $indicators[] = Indicator::make('Tutar ≥ '.TurkishFormat::tl($data['min']))->removeField('min');
                    }
                    if (filled($data['max'] ?? null)) {
                        $indicators[] = Indicator::make('Tutar ≤ '.TurkishFormat::tl($data['max']))->removeField('max');
                    }

                    return $indicators;
                }),
            Filter::make('firma_ziyareti')
                ->label('Firma ziyareti')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('from')->label('Ziyaret başlangıcı')->native(false)->displayFormat('d.m.Y'),
                        DatePicker::make('until')->label('Ziyaret bitişi')->native(false)->displayFormat('d.m.Y'),
                    ]),
                ])
                ->columnSpan(2)
                ->query(fn (Builder $query, array $data) => $query
                    ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('firma_ziyareti', '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('firma_ziyareti', '<=', $date)))
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['from'] ?? null) {
                        $indicators[] = Indicator::make('Ziyaret '.Carbon::parse($data['from'])->format('d.m.Y').' sonrası')->removeField('from');
                    }
                    if ($data['until'] ?? null) {
                        $indicators[] = Indicator::make('Ziyaret '.Carbon::parse($data['until'])->format('d.m.Y').' öncesi')->removeField('until');
                    }

                    return $indicators;
                }),
            TrashedFilter::make()
                ->label('Silinen kayıtlar')
                ->visible(fn () => Gate::allows('restoreAny', Customer::class)),
        ];
    }

    /**
     * @param  class-string<\BackedEnum&HasLabel>  $enum
     */
    private static function changeStatusAction(string $field, string $label, string $enum, Heroicon $icon): BulkAction
    {
        return BulkAction::make($field.'Degistir')
            ->label($label)
            ->icon($icon)
            ->visible(fn () => Gate::allows('updateAny', Customer::class))
            ->modalHeading($label)
            ->modalSubmitActionLabel('Uygula')
            ->schema([
                Select::make('value')->label('Yeni durum')->options($enum)->required(),
            ])
            ->action(function (Collection $records, array $data) use ($field) {
                self::updateStatus($records, $field, $data['value']);

                Notification::make()
                    ->title($records->count().' müşteri güncellendi')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Seçilen kayıtlarda bir durum alanını değiştirir. Liste ve harita ekranı aynı kuralı kullanır.
     * Model olayları çalışsın diye tek tek güncellenir; denetim kaydı her kayıt için yazılır.
     *
     * @param  iterable<mixed>  $records
     * @return int Güncellenen kayıt sayısı
     */
    public static function updateStatus(iterable $records, string $field, mixed $value): int
    {
        $count = 0;

        foreach ($records as $customer) {
            if ($customer instanceof Customer && Gate::allows('update', $customer)) {
                $customer->update([$field => $value]);
                $count++;
            }
        }

        return $count;
    }

    private static function isFiltered(Table $table): bool
    {
        $livewire = $table->getLivewire();

        return count($table->getFilterIndicators()) > 0
            || ($livewire instanceof ListRecords && filled($livewire->getTableSearch()));
    }
}
