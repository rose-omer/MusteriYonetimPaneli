<?php

namespace App\Filament\Widgets;

use App\Enums\TehlikeSinifi;
use App\Models\Customer;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

/**
 * Tehlike sınıfına göre müşteri sayısı. Sıralı (az → çok) kategoriler tek renkte
 * yatay çubukla gösterilir: kimlik eksendeki etikette, renk yalnız miktarı taşır.
 */
class TehlikeSinifiChart extends ChartWidget
{
    protected static ?int $sort = 2;

    // Sorgular hafif; iskelet yanıp sönmesin diye hemen yüklenir.
    protected static bool $isLazy = false;

    protected ?string $heading = 'Tehlike sınıfına göre';

    protected ?string $description = 'Firma sayısı';

    protected ?string $maxHeight = '240px';

    protected ?string $emptyStateHeading = 'Henüz veri yok';

    protected ?string $emptyStateDescription = 'Tehlike sınıfı girilmiş müşteri olunca dağılım burada görünür.';

    protected string|\BackedEnum|null $emptyStateIcon = Heroicon::OutlinedShieldExclamation;

    protected ?string $pollingInterval = null;

    /** @return array<string, int> */
    private function counts(): array
    {
        $raw = Customer::query()
            ->whereNotNull('tehlike_sinifi')
            ->selectRaw('tehlike_sinifi, count(*) as adet')
            ->groupBy('tehlike_sinifi')
            ->pluck('adet', 'tehlike_sinifi');

        $counts = [];
        foreach (TehlikeSinifi::cases() as $case) {
            $counts[$case->getLabel()] = (int) ($raw[$case->value] ?? 0);
        }

        return array_sum($counts) === 0 ? [] : $counts;
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
                    'backgroundColor' => '#1f7e94',
                    'hoverBackgroundColor' => '#0f6d82',
                    'barThickness' => 22,
                ],
            ],
            'labels' => array_keys($counts),
        ];
    }

    public function getChartAssistiveContent(): HtmlString
    {
        $rows = collect($this->counts())
            ->map(fn (int $adet, string $label) => '<tr><th scope="row">'.e($label).'</th><td>'.$adet.'</td></tr>')
            ->join('');

        return new HtmlString('<table><caption>Tehlike sınıfına göre firma sayısı</caption><thead><tr><th scope="col">Sınıf</th><th scope="col">Firma</th></tr></thead><tbody>'.$rows.'</tbody></table>');
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
