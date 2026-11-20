<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Support\TurkishFormat;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Müşteri')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Firma')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->schema([
                                Section::make()
                                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                                    ->schema([
                                        TextEntry::make('hizmet_alan_isyeri_unvani')->label('Unvan')->weight('semibold')->columnSpan(['md' => 2]),
                                        TextEntry::make('firma_no')->label('Firma no')->placeholder('Yok')->copyable()->fontFamily(FontFamily::Mono)->extraAttributes(['class' => 'tabular-nums']),
                                        TextEntry::make('gorunur_unvani')->label('Görünür unvan')->placeholder('Yok'),
                                        TextEntry::make('faaliyet.isim')->label('Faaliyet')->placeholder('Seçilmedi'),
                                        TextEntry::make('tehlike_sinifi')->label('Tehlike sınıfı')->badge()->placeholder('Belirtilmedi'),
                                        TextEntry::make('calisan_sayisi')->label('Çalışan sayısı')->numeric(locale: 'tr')->placeholder('Bilinmiyor')->fontFamily(FontFamily::Mono)->extraAttributes(['class' => 'tabular-nums']),
                                    ]),
                                Section::make('İletişim')
                                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                                    ->schema([
                                        TextEntry::make('firma_sorumlusu')->label('Firma sorumlusu')->placeholder('Yok'),
                                        TextEntry::make('telefon_numarasi')
                                            ->label('Yetkili telefonu')
                                            ->formatStateUsing(fn (?string $state) => CustomerResource::phoneForCurrentUser($state))
                                            ->fontFamily(FontFamily::Mono)->extraAttributes(['class' => 'tabular-nums'])
                                            ->placeholder('Yok'),
                                        TextEntry::make('il')
                                            ->label('Adres')
                                            ->formatStateUsing(fn (Customer $record) => collect([$record->mahalle, $record->il])->filter()->join(', '))
                                            ->placeholder('Yok'),
                                        TextEntry::make('konum_url')
                                            ->label('Konum')
                                            ->formatStateUsing(fn () => 'Haritada aç')
                                            ->placeholder('Bağlantı yok')
                                            ->suffixAction(
                                                Action::make('haritadaAc')
                                                    ->label('Haritada aç')
                                                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                                                    ->url(fn (Customer $record) => $record->konum_url, shouldOpenInNewTab: true)
                                                    ->extraAttributes(['rel' => 'noopener noreferrer'])
                                                    ->visible(fn (Customer $record) => filled($record->konum_url)),
                                            ),
                                    ]),
                                Section::make('Saha')
                                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                                    ->schema([
                                        TextEntry::make('igu')->label('İş güvenliği uzmanı')->placeholder('Atanmadı'),
                                        TextEntry::make('ih')->label('İşyeri hekimi')->placeholder('Atanmadı'),
                                        TextEntry::make('firma_ziyareti')->label('Son firma ziyareti')->date('d.m.Y')->placeholder('Ziyaret yok')->fontFamily(FontFamily::Mono)->extraAttributes(['class' => 'tabular-nums']),
                                        TextEntry::make('defter')->label('Defter')->placeholder('Yok'),
                                        TextEntry::make('ra')->label('Risk analizi')->placeholder('Yok'),
                                    ]),
                            ]),
                        Tab::make('Fatura ve ödeme')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                Section::make()
                                    ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                                    ->schema([
                                        TextEntry::make('tutar')->label('Tutar')->formatStateUsing(fn ($state) => TurkishFormat::tl($state, 2))->placeholder('Girilmedi')->weight('semibold')->fontFamily(FontFamily::Mono)->extraAttributes(['class' => 'tabular-nums']),
                                        TextEntry::make('odeme_durumu')->label('Ödeme durumu')->badge()->placeholder('Belirtilmedi'),
                                        TextEntry::make('fatura_tipi')->label('Fatura tipi')->placeholder('Belirtilmedi'),
                                        TextEntry::make('fatura_durumu')->label('Fatura durumu')->badge()->placeholder('Belirtilmedi'),
                                        TextEntry::make('sozlesme_onay_durumu')->label('Sözleşme onayı')->badge()
                                            ->formatStateUsing(fn (bool $state) => $state ? 'Onaylandı' : 'Onaylanmadı')
                                            ->color(fn (bool $state) => $state ? 'gray' : 'vurgu'),
                                    ]),
                            ]),
                        Tab::make('Geçmiş')
                            ->icon(Heroicon::OutlinedClock)
                            ->badge(fn (Customer $record) => $record->audits()->count() ?: null)
                            ->schema([
                                ViewEntry::make('audits')
                                    ->hiddenLabel()
                                    ->view('filament.customers.audit-log'),
                            ]),
                    ]),
            ]);
    }
}
