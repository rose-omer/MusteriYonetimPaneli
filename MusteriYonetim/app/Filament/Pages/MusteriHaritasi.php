<?php

namespace App\Filament\Pages;

use App\Enums\FaturaDurumu;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Exports\CustomersExport;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use App\Support\IlKoordinatlari;
use App\Support\TurkishFormat;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Şematik Türkiye haritası, müşteri listesi ve seçili firmanın künyesi tek ekranda.
 * Daire boyutu ildeki açık tutarı, tonu ildeki çoğunluk tehlike sınıfını gösterir.
 *
 * @property-read array{adet: int, tutar: float} $isaretliOzet
 */
class MusteriHaritasi extends Page
{
    /** Listede bir seferde gösterilen satır. */
    public const SAYFA = 30;

    /** En büyük dairenin yarıçapı (çizim birimi). */
    private const R_MAKS = 24.0;

    private const R_MIN = 6.0;

    protected static ?string $slug = 'harita';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Map;

    protected static ?string $navigationLabel = 'Harita';

    protected static ?string $title = 'Müşteri haritası';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.musteri-haritasi';

    #[Url(as: 'q', except: '')]
    public string $arama = '';

    #[Url(except: '')]
    public string $tehlike = '';

    #[Url(except: '')]
    public string $il = '';

    #[Url(except: '')]
    public string $odeme = '';

    #[Url(except: 'unvan')]
    public string $sirala = 'unvan';

    #[Url(as: 'firma', except: null)]
    public ?int $secili = null;

    /** @var list<int|string> Toplu işlem için işaretlenen kayıtlar. */
    public array $isaretli = [];

    public int $limit = self::SAYFA;

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', Customer::class);
    }

    /** Başlık sayfanın kendi üst satırında çizilir (arama kutusuyla aynı satır). */
    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['arama', 'tehlike', 'il', 'odeme', 'sirala'], true)) {
            $this->limit = self::SAYFA;
        }
    }

    public function tehlikeSec(string $deger): void
    {
        $this->tehlike = TehlikeSinifi::tryFrom($deger) ? $deger : '';
        $this->limit = self::SAYFA;
    }

    public function ilSec(string $il): void
    {
        $kanonik = IlKoordinatlari::kanonik($il) ?? '';
        $this->il = $this->il === $kanonik ? '' : $kanonik;
        $this->limit = self::SAYFA;
    }

    public function sec(int $id): void
    {
        $this->secili = $id;
        unset($this->seciliFirma);
        $this->dispatch('firma-secildi');
    }

    public function kapat(): void
    {
        $this->secili = null;
    }

    public function dahaFazla(): void
    {
        $this->limit += self::SAYFA;
    }

    public function siralamaDegistir(): void
    {
        $this->sirala = $this->sirala === 'unvan' ? 'tutar' : 'unvan';
    }

    public function secimiKaldir(): void
    {
        $this->isaretli = [];
    }

    public function filtreleriTemizle(): void
    {
        $this->reset('arama', 'tehlike', 'il', 'odeme');
        $this->limit = self::SAYFA;
    }

    public function filtreliMi(): bool
    {
        return $this->arama !== '' || $this->tehlike !== '' || $this->il !== '' || $this->odeme !== '';
    }

    /**
     * Üst şerit: bütün kayıtlar üzerinden, filtreden bağımsız.
     *
     * @return array{acikTutar: float, acikFirma: int, ziyaret: int, onaysiz: int, toplam: int}
     */
    #[Computed]
    public function ozet(): array
    {
        $acik = self::acikDegerler();

        return [
            'acikTutar' => (float) Customer::whereIn('odeme_durumu', $acik)->sum('tutar'),
            'acikFirma' => Customer::whereIn('odeme_durumu', $acik)->count(),
            'ziyaret' => Customer::whereDate('firma_ziyareti', '>=', now()->startOfMonth()->toDateString())
                ->whereDate('firma_ziyareti', '<=', now()->endOfMonth()->toDateString())
                ->count(),
            'onaysiz' => Customer::where('sozlesme_onay_durumu', false)->count(),
            'toplam' => Customer::count(),
        ];
    }

    /** @return array{acik: string, ziyaret: string, onaysiz: string} */
    public function ozetBaglantilari(): array
    {
        return [
            'acik' => CustomerResource::getUrl('index', ['filters' => ['odeme_durumu' => ['values' => self::acikDegerler()]]]),
            'ziyaret' => CustomerResource::getUrl('index', ['filters' => ['firma_ziyareti' => [
                'from' => now()->startOfMonth()->toDateString(),
                'until' => now()->endOfMonth()->toDateString(),
            ]]]),
            'onaysiz' => CustomerResource::getUrl('index', ['filters' => ['sozlesme_onay_durumu' => ['value' => '0']]]),
        ];
    }

    /**
     * İl daireleri. İl filtresi uygulanmaz; seçili il halkayla gösterilir.
     *
     * @return array{iller: list<array{ad: string, x: float, y: float, r: float, adet: int, acik: float, sinif: TehlikeSinifi|null, etiket: array{x: float, y: float, yon: string}}>, disarida: int}
     */
    #[Computed]
    public function harita(): array
    {
        $acik = self::acikDegerler();
        $satirlar = $this->tabanSorgu()
            ->whereNotNull('il')
            ->selectRaw('il, tehlike_sinifi, count(*) as adet, sum(case when odeme_durumu in (?, ?) then tutar else 0 end) as acik', $acik)
            ->groupBy('il', 'tehlike_sinifi')
            ->toBase()
            ->get();

        /** @var array<string, array{adet: int, acik: float, siniflar: array<string, int>}> $iller */
        $iller = [];
        $disarida = 0;

        foreach ($satirlar as $satir) {
            $ad = IlKoordinatlari::kanonik((string) $satir->il);
            $adet = (int) $satir->adet;

            if ($ad === null) {
                $disarida += $adet;

                continue;
            }

            $iller[$ad] ??= ['adet' => 0, 'acik' => 0.0, 'siniflar' => []];
            $iller[$ad]['adet'] += $adet;
            $iller[$ad]['acik'] += (float) $satir->acik;

            if ($satir->tehlike_sinifi !== null) {
                $iller[$ad]['siniflar'][(string) $satir->tehlike_sinifi] = ($iller[$ad]['siniflar'][(string) $satir->tehlike_sinifi] ?? 0) + $adet;
            }
        }

        $enBuyuk = max([0.0, ...array_column($iller, 'acik')]);
        $sonuc = [];

        foreach ($iller as $ad => $veri) {
            $nokta = IlKoordinatlari::nokta($ad);

            if ($nokta === null) {
                continue;
            }

            // Alan tutarla orantılı: yarıçap karekökle büyür.
            $r = $enBuyuk > 0 ? max(self::R_MIN, self::R_MAKS * sqrt($veri['acik'] / $enBuyuk)) : self::R_MIN;

            $sonuc[] = [
                'ad' => $ad,
                'x' => $nokta['x'],
                'y' => $nokta['y'],
                'r' => round($r, 1),
                'adet' => $veri['adet'],
                'acik' => $veri['acik'],
                'sinif' => self::cogunluk($veri['siniflar']),
                'etiket' => $nokta['etiket'],
            ];
        }

        // Büyük daire altta kalır; küçük daire görünür.
        usort($sonuc, fn (array $a, array $b) => $b['r'] <=> $a['r']);

        return ['iller' => self::etiketleriYerlestir($sonuc), 'disarida' => $disarida];
    }

    /**
     * Etiketi başka daireye ve etikete binmeyecek yere koyar. Önce tablodaki yer denenir,
     * sonra sırayla öteki yerler; hiçbiri boş değilse en az çakışan yer seçilir.
     *
     * @param  list<array{ad: string, x: float, y: float, r: float, adet: int, acik: float, sinif: TehlikeSinifi|null, etiket: string}>  $iller
     * @return list<array{ad: string, x: float, y: float, r: float, adet: int, acik: float, sinif: TehlikeSinifi|null, etiket: array{x: float, y: float, yon: string}}>
     */
    private static function etiketleriYerlestir(array $iller): array
    {
        $yerler = ['sag', 'sol', 'ust', 'alt', 'ust-sag', 'ust-sol', 'alt-sag', 'alt-sol'];
        $kutular = [];
        $sonuc = [];

        foreach ($iller as $i => $il) {
            // 13 px Instrument Sans ortalama harf genişliği + beyaz kontur payı.
            $genislik = mb_strlen($il['ad']) * 7.0 + 3;
            $enIyi = null;
            $enAzCeza = INF;

            // Önce daireye bitişik yerler, sonra 8 birim uzağı.
            foreach ([0, 8] as $ek) {
                foreach (array_unique([$il['etiket'], ...$yerler]) as $yer) {
                    $etiket = self::etiketKonumu($il['x'], $il['y'], $il['r'] + $ek, $yer);
                    $kutu = self::etiketKutusu($etiket, $genislik);
                    $ceza = self::cakisma($kutu, $iller, $i, $kutular) + $ek / 10;

                    if ($ceza < $enAzCeza) {
                        [$enIyi, $enAzCeza] = [[$etiket, $kutu], $ceza];
                    }

                    if ($ceza === 0.0) {
                        break 2;
                    }
                }
            }

            $sonuc[] = [...$il, 'etiket' => $enIyi[0]];
            $kutular[] = $enIyi[1];
        }

        return $sonuc;
    }

    /**
     * @param  array{x: float, y: float, yon: string}  $etiket
     * @return array{0: float, 1: float, 2: float, 3: float} x0, y0, x1, y1
     */
    private static function etiketKutusu(array $etiket, float $genislik): array
    {
        $x0 = match ($etiket['yon']) {
            'end' => $etiket['x'] - $genislik,
            'middle' => $etiket['x'] - $genislik / 2,
            default => $etiket['x'],
        };

        // 13 px yazının üst ve alt payı (aksanlı büyük harf, alt uzantı) + kontur.
        return [$x0, $etiket['y'] - 13, $x0 + $genislik, $etiket['y'] + 5];
    }

    /**
     * @param  array{0: float, 1: float, 2: float, 3: float}  $kutu
     * @param  list<array{x: float, y: float, r: float}>  $iller
     * @param  list<array{0: float, 1: float, 2: float, 3: float}>  $kutular
     */
    private static function cakisma(array $kutu, array $iller, int $kendisi, array $kutular): float
    {
        [$x0, $y0, $x1, $y1] = $kutu;
        $ceza = 0.0;

        if ($x0 < 2 || $y0 < 2 || $x1 > IlKoordinatlari::GENISLIK - 2 || $y1 > IlKoordinatlari::YUKSEKLIK - 2) {
            $ceza += 1000;
        }

        foreach ($iller as $j => $il) {
            $d = hypot(max($x0, min($il['x'], $x1)) - $il['x'], max($y0, min($il['y'], $y1)) - $il['y']);
            $pay = $il['r'] + ($j === $kendisi ? 1 : 3);
            $ceza += max(0.0, $pay - $d);
        }

        foreach ($kutular as [$a0, $b0, $a1, $b1]) {
            $ceza += max(0.0, min($x1, $a1) - max($x0, $a0)) * max(0.0, min($y1, $b1) - max($y0, $b0)) / 10;
        }

        return $ceza;
    }

    /** @return Collection<int, Customer> */
    #[Computed]
    public function liste(): Collection
    {
        $sorgu = $this->listeSorgu();

        $this->sirala === 'tutar'
            ? $sorgu->orderByDesc('tutar')->orderBy('hizmet_alan_isyeri_unvani')
            : $sorgu->orderBy('hizmet_alan_isyeri_unvani');

        return $sorgu->limit($this->limit)->get();
    }

    #[Computed]
    public function listeToplam(): int
    {
        return $this->listeSorgu()->count();
    }

    #[Computed]
    public function seciliFirma(): ?Customer
    {
        if ($this->secili === null) {
            return null;
        }

        $firma = Customer::with('faaliyet')->find($this->secili);

        return $firma !== null && Gate::allows('view', $firma) ? $firma : null;
    }

    /** @return array{adet: int, tutar: float} */
    #[Computed]
    public function isaretliOzet(): array
    {
        $kayitlar = $this->isaretliKayitlar();

        return ['adet' => $kayitlar->count(), 'tutar' => (float) $kayitlar->sum('tutar')];
    }

    /** Toplu işlem çubuğunda en az bir işlem görünür mü? */
    public function topluIslemVar(): bool
    {
        return Gate::allows('updateAny', Customer::class)
            || Gate::allows('export', Customer::class)
            || Gate::allows('deleteAny', Customer::class);
    }

    public function telefonGosterimi(?string $telefon): ?string
    {
        return CustomerResource::phoneForCurrentUser($telefon);
    }

    public function telefonMaskeli(): bool
    {
        return ! CustomerResource::currentUserSeesFullPhone();
    }

    public function ekleAction(): Action
    {
        return Action::make('ekle')
            ->label('Müşteri ekle')
            ->icon(Heroicon::Plus)
            ->url(CustomerResource::getUrl('create'))
            ->visible(fn () => Gate::allows('create', Customer::class));
    }

    public function odemeAction(): Action
    {
        return $this->durumAction('odeme', 'odeme_durumu', 'Ödeme durumunu değiştir', OdemeDurumu::class);
    }

    public function faturaAction(): Action
    {
        return $this->durumAction('fatura', 'fatura_durumu', 'Fatura durumunu değiştir', FaturaDurumu::class);
    }

    public function excelAction(): Action
    {
        return Action::make('excel')
            ->label("Excel'e aktar")
            ->visible(fn () => Gate::allows('export', Customer::class))
            ->action(function (): ?BinaryFileResponse {
                $kayitlar = $this->isaretliKayitlar();

                if ($kayitlar->isEmpty()) {
                    return null;
                }

                return Excel::download(new CustomersExport($kayitlar->load('faaliyet')), 'musteriler-'.now()->format('Y-m-d').'.xlsx');
            });
    }

    public function silAction(): Action
    {
        return Action::make('sil')
            ->label('Seçilenleri sil')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn () => $this->isaretliOzet['adet'].' müşteriyi sil')
            ->modalDescription('Silinen kayıt 90 gün içinde müşteri listesinden geri alınabilir, sonra kalıcı silinir.')
            ->modalSubmitActionLabel('Sil')
            ->visible(fn () => Gate::allows('deleteAny', Customer::class))
            ->action(function () {
                $silinen = 0;

                foreach ($this->isaretliKayitlar() as $firma) {
                    if (Gate::allows('delete', $firma)) {
                        $firma->delete();
                        $silinen++;
                    }
                }

                if ($this->secili !== null && in_array($this->secili, array_map('intval', $this->isaretli), true)) {
                    $this->secili = null;
                }

                $this->isaretli = [];
                $this->tazele();

                Notification::make()->title($silinen.' müşteri silindi')->success()->send();
            });
    }

    /**
     * @param  class-string<OdemeDurumu|FaturaDurumu>  $enum
     */
    private function durumAction(string $ad, string $alan, string $baslik, string $enum): Action
    {
        return Action::make($ad)
            ->label($baslik)
            ->modalHeading($baslik)
            ->modalDescription(fn () => $this->isaretliOzet['adet'].' müşteri seçili.')
            ->modalSubmitActionLabel('Uygula')
            ->modalWidth('md')
            ->schema([
                Select::make('value')->label('Yeni durum')->options($enum)->required(),
            ])
            ->visible(fn () => Gate::allows('updateAny', Customer::class))
            ->action(function (array $data) use ($alan) {
                $sayi = CustomersTable::updateStatus($this->isaretliKayitlar(), $alan, $data['value']);
                $this->isaretli = [];
                $this->tazele();

                Notification::make()->title($sayi.' müşteri güncellendi')->success()->send();
            });
    }

    /** @return Collection<int, Customer> */
    private function isaretliKayitlar(): Collection
    {
        $idler = array_values(array_filter(array_map('intval', $this->isaretli)));

        return $idler === [] ? new Collection : Customer::whereIn('id', $idler)->get();
    }

    private function tazele(): void
    {
        unset($this->ozet, $this->harita, $this->liste, $this->listeToplam, $this->seciliFirma, $this->isaretliOzet);
    }

    /** @return Builder<Customer> */
    private function tabanSorgu(): Builder
    {
        $arama = trim($this->arama);
        $telefonla = CustomerResource::currentUserSeesFullPhone();

        return Customer::query()
            ->when($arama !== '', fn (Builder $q) => $q->where(function (Builder $w) use ($arama, $telefonla) {
                $w->where('hizmet_alan_isyeri_unvani', 'like', '%'.$arama.'%')
                    ->orWhere('gorunur_unvani', 'like', '%'.$arama.'%')
                    ->orWhere('firma_no', 'like', '%'.$arama.'%');

                // KVKK: telefonu tam göremeyen kullanıcı telefonla arayamaz.
                $rakam = preg_replace('/\D/', '', $arama) ?? '';
                if ($telefonla && strlen($rakam) >= 3) {
                    $w->orWhere('telefon_numarasi', 'like', '%'.$rakam.'%');
                }
            }))
            ->when(TehlikeSinifi::tryFrom($this->tehlike), fn (Builder $q, TehlikeSinifi $t) => $q->where('tehlike_sinifi', $t))
            ->when($this->odeme === 'acik', fn (Builder $q) => $q->whereIn('odeme_durumu', self::acikDegerler()))
            ->when(OdemeDurumu::tryFrom($this->odeme), fn (Builder $q, OdemeDurumu $o) => $q->where('odeme_durumu', $o));
    }

    /** @return Builder<Customer> */
    private function listeSorgu(): Builder
    {
        $sorgu = $this->tabanSorgu();

        if ($this->il !== '') {
            $sorgu->whereIn('il', $this->ilYazimlari($this->il));
        }

        return $sorgu;
    }

    /**
     * Veritabanında aynı ilin farklı yazımları olabilir ("İstanbul", "istanbul").
     *
     * @return list<string>
     */
    private function ilYazimlari(string $kanonik): array
    {
        return Customer::query()
            ->whereNotNull('il')
            ->distinct()
            ->pluck('il')
            ->filter(fn ($il) => IlKoordinatlari::kanonik((string) $il) === $kanonik)
            ->values()
            ->all() ?: [$kanonik];
    }

    /** Ortak para biçimi; ayrıntı: TurkishFormat::tl(). */
    public function tl(float|string|null $tutar, int $ondalik = 0): string
    {
        return TurkishFormat::tl($tutar, $ondalik);
    }

    /** @return array{x: float, y: float, yon: string} */
    private static function etiketKonumu(float $x, float $y, float $r, string $yer): array
    {
        [$ex, $ey, $yon] = match ($yer) {
            'sol' => [$x - $r - 5, $y + 4.5, 'end'],
            'ust' => [$x, $y - $r - 6, 'middle'],
            'alt' => [$x, $y + $r + 15, 'middle'],
            'ust-sol' => [$x - $r * 0.4, $y - $r - 6, 'end'],
            'ust-sag' => [$x + $r * 0.4, $y - $r - 6, 'start'],
            'alt-sol' => [$x - $r * 0.4, $y + $r + 15, 'end'],
            'alt-sag' => [$x + $r * 0.4, $y + $r + 15, 'start'],
            default => [$x + $r + 5, $y + 4.5, 'start'],
        };

        return ['x' => round($ex, 1), 'y' => round($ey, 1), 'yon' => $yon];
    }

    /** @return list<string> */
    private static function acikDegerler(): array
    {
        return array_map(fn (OdemeDurumu $d) => $d->value, OdemeDurumu::acik());
    }

    /**
     * Çoğunluk sınıfı; eşitlikte daha yüksek tehlike seçilir.
     *
     * @param  array<string, int>  $siniflar
     */
    private static function cogunluk(array $siniflar): ?TehlikeSinifi
    {
        $secilen = null;
        $enCok = 0;

        foreach (TehlikeSinifi::cases() as $sinif) {
            $adet = $siniflar[$sinif->value] ?? 0;

            if ($adet > 0 && $adet >= $enCok) {
                $secilen = $sinif;
                $enCok = $adet;
            }
        }

        return $secilen;
    }
}
