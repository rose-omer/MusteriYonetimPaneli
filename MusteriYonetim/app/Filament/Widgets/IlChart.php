<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

/** Müşteri sayısına göre ilk 10 il. */
class IlChart extends ChartWidget
{
    protected static ?int $sort = 3;

    // Sorgular hafif; iskelet yanıp sönmesin diye hemen yüklenir.
    protected static bool $isLazy = false;

    protected ?string $heading = 'İllere göre ilk 10';

    protected ?string $description = 'Firma sayısı';

    protected ?string $maxHeight = '240px';

    protected ?string $emptyStateHeading = 'Henüz veri yok';

    protected ?string $emptyStateDescription = 'İl bilgisi girilmiş müşteri olunca liste burada görünür.';

    protected string|\BackedEnum|null $emptyStateIcon = Heroicon::OutlinedMapPin;

    protected ?string $pollingInterval = null;

    /** @return array<string, int> */
    private function counts(): array
    {
        return Customer::query()
            ->whereNotNull('il')
            ->selectRaw('il, count(*) as adet')
            ->groupBy('il')
            ->orderByDesc('adet')
            ->orderBy('il')
            ->limit(10)
            ->pluck('adet', 'il')
            ->map(fn ($adet) => (int) $adet)
            ->all();
    }

    protected function getData(): array
    {
        $counts = $this->counts();

        if ($counts === []) {
            return [];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Firma',
                    'data' => array_values($counts),
                    'borderRadius' => 4,
                    'borderWidth' => 0,
                    // primary-500: beyaz ve koyu yüzeyde 3:1 üstü.
                    'backgroundColor' => '#427485',
                    'hoverBackgroundColor' => '#5e95a6',
                    'maxBarThickness' => 16,
                ],
            ],
            'labels' => array_keys($counts),
        ];
    }

    public function getChartAssistiveContent(): HtmlString
    {
        $rows = collect($this->counts())
            ->map(fn (int $adet, string $il) => '<tr><th scope="row">'.e($il).'</th><td>'.$adet.'</td></tr>')
            ->join('');

        return new HtmlString('<table><caption>İllere göre firma sayısı</caption><thead><tr><th scope="col">İl</th><th scope="col">Firma</th></tr></thead><tbody>'.$rows.'</tbody></table>');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            // Eksen etiketi 12 px: Chart.js varsayılanı (10-11 px) tabloda okunmuyordu.
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0, 'font' => ['size' => 12]], 'grid' => ['drawTicks' => false]],
                'y' => ['ticks' => ['font' => ['size' => 12]], 'grid' => ['display' => false]],
            ],
        ];
    }
}
