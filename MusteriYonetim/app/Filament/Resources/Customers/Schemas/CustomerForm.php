<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\FaturaDurumu;
use App\Enums\FaturaTipi;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Müşteri formu: ekleme ve düzenleme aynı şemayı kullanır, seçenekler enum'lardan gelir.
 */
class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Firma')
                    ->description('Unvan zorunlu. Firma no her firmada tekil olmalı.')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                    ->schema([
                        TextInput::make('hizmet_alan_isyeri_unvani')
                            ->label('Hizmet alan işyeri unvanı')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['md' => 2]),
                        TextInput::make('firma_no')
                            ->label('Firma no')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('gorunur_unvani')
                            ->label('Görünür unvan')
                            ->helperText('Listede kısa ad olarak görünür.')
                            ->maxLength(255)
                            ->columnSpan(['md' => 2]),
                        Select::make('faaliyet_id')
                            ->label('Faaliyet')
                            ->relationship('faaliyet', 'isim')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (): ?array => self::canManageFaaliyet() ? [
                                TextInput::make('isim')->label('Faaliyet adı')->required()->maxLength(255)->unique('faaliyets', 'isim'),
                            ] : null),
                        TextInput::make('calisan_sayisi')
                            ->label('Çalışan sayısı')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000),
                        Select::make('tehlike_sinifi')
                            ->label('Tehlike sınıfı')
                            ->options(TehlikeSinifi::class),
                    ]),

                Section::make('İletişim')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                    ->schema([
                        TextInput::make('firma_sorumlusu')
                            ->label('Firma sorumlusu')
                            ->maxLength(255),
                        TextInput::make('telefon_numarasi')
                            ->label('Yetkili telefonu')
                            ->tel()
                            ->mask('9999 999 99 99')
                            ->placeholder('0532 123 45 67')
                            ->regex('/^[\d\s()+-]+$/')
                            ->maxLength(30),
                        TextInput::make('il')
                            ->label('İl')
                            ->maxLength(100)
                            ->datalist(['Adana', 'Ankara', 'Antalya', 'Bursa', 'Denizli', 'Eskişehir', 'Gaziantep', 'İstanbul', 'İzmir', 'Kayseri', 'Kocaeli', 'Konya', 'Manisa', 'Mersin', 'Sakarya', 'Tekirdağ']),
                        TextInput::make('mahalle')
                            ->label('Mahalle')
                            ->maxLength(255),
                        TextInput::make('konum_url')
                            ->label('Konum bağlantısı')
                            ->helperText('Harita bağlantısı. Yalnız https:// ile başlayan adres kabul edilir.')
                            ->url()
                            ->rule('url:https')
                            ->maxLength(2048)
                            ->columnSpan(['md' => 2]),
                    ]),

                Section::make('Fatura ve ödeme')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                    ->schema([
                        Select::make('fatura_tipi')
                            ->label('Fatura tipi')
                            ->options(FaturaTipi::class),
                        Select::make('fatura_durumu')
                            ->label('Fatura durumu')
                            ->options(FaturaDurumu::class),
                        TextInput::make('tutar')
                            ->label('Tutar')
                            ->numeric()
                            ->inputMode('decimal')
                            ->minValue(0)
                            ->maxValue(99999999.99)
                            ->step(0.01)
                            ->suffix('₺'),
                        Select::make('odeme_durumu')
                            ->label('Ödeme durumu')
                            ->options(OdemeDurumu::class),
                    ]),

                Section::make('Saha')
                    ->description('İş güvenliği uzmanı, işyeri hekimi ve ziyaret bilgileri.')
                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                    ->schema([
                        TextInput::make('igu')
                            ->label('İş güvenliği uzmanı (İGU)')
                            ->maxLength(255),
                        TextInput::make('ih')
                            ->label('İşyeri hekimi (İH)')
                            ->maxLength(255),
                        DatePicker::make('firma_ziyareti')
                            ->label('Son firma ziyareti')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->maxDate(now()->addYear()),
                        TextInput::make('defter')
                            ->label('Defter')
                            ->maxLength(255),
                        TextInput::make('ra')
                            ->label('Risk analizi (RA)')
                            ->maxLength(255),
                        Toggle::make('sozlesme_onay_durumu')
                            ->label('Sözleşme onaylandı')
                            ->inline(false),
                    ]),
            ]);
    }

    private static function canManageFaaliyet(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->isAdmin();
    }
}
