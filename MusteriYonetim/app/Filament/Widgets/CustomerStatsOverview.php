<?php

namespace App\Filament\Widgets;

use App\Enums\OdemeDurumu;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class CustomerStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    /**
     * Sütun sayısı sayfa genişliğine değil kapsayıcıya bağlı: kenar çubuğu açıkken dar alanda
     * dört kart sığmaz, tutar kesilir. Kapsayıcı sorgusu olmayan tarayıcıda görünüm genişliği kullanılır.
     *
     * @return array<string, int>
     */
    protected function getColumns(): array
    {
        return ['@xl' => 2, '@5xl' => 4, '!@md' => 2, '!@lg' => 4];
    }

    protected function getStats(): array
    {
        $total = Customer::count();
        $acik = array_map(fn (OdemeDurumu $d) => $d->value, OdemeDurumu::acik());
        $acikTutar = (float) Customer::whereIn('odeme_durumu', $acik)->sum('tutar');
        $acikSayisi = Customer::whereIn('odeme_durumu', $acik)->count();
        $buAy = Customer::where('created_at', '>=', now()->startOfMonth())->count();
        $onaysiz = Customer::where('sozlesme_onay_durumu', false)->count();

        if ($total === 0) {
            return [
                Stat::make('Toplam müşteri', '0')
                    ->description('Henüz müşteri yok. Listeyi içe aktararak başlayın.')
                    ->url(CustomerResource::getUrl('index')),
            ];
        }

        return [
            Stat::make('Toplam müşteri', Number::format($total, locale: 'tr'))
                ->description('Bütün kayıtlı firmalar')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->url(CustomerResource::getUrl('index')),
            Stat::make('Ödenmemiş tutar', Number::currency($acikTutar, in: 'TRY', locale: 'tr', precision: 0))
                ->description($acikSayisi.' firmada ödeme bekliyor')
                ->descriptionColor($acikSayisi > 0 ? 'danger' : 'gray')
                ->icon(Heroicon::OutlinedBanknotes)
                ->url(CustomerResource::getUrl('index', ['filters' => ['odeme_durumu' => ['values' => $acik]]])),
            Stat::make('Bu ay eklenen', Number::format($buAy, locale: 'tr'))
                ->description(now()->locale('tr')->translatedFormat('F Y'))
                ->icon(Heroicon::OutlinedCalendarDays)
                ->url(CustomerResource::getUrl('index', ['sort' => 'created_at:desc'])),
            Stat::make('Sözleşmesi onaysız', Number::format($onaysiz, locale: 'tr'))
                ->description($onaysiz > 0 ? 'Onay bekleyen firmalar' : 'Bütün sözleşmeler onaylı')
                ->descriptionColor($onaysiz > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedDocumentCheck)
                ->url(CustomerResource::getUrl('index', ['filters' => ['sozlesme_onay_durumu' => ['value' => '0']]])),
        ];
    }
}
