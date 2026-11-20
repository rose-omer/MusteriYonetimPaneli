<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Şematik Türkiye haritası için sabit veri: 81 il merkezi, kaba sınır çizgisi ve Marmara.
 * Dış harita servisi ya da harita karosu kullanılmaz; konumlar il merkezidir.
 *
 * İzdüşüm: eşdikdörtgen, 39° enlemde düzeltilmiş (x: 43,66 px/derece boylam, y: 56,5 px/derece enlem).
 * Çizim alanı 860 × 380 birimdir.
 */
final class IlKoordinatlari
{
    public const GENISLIK = 860;

    public const YUKSEKLIK = 380;

    private const KX = 43.66;

    private const KY = 56.5;

    private const BOYLAM0 = 25.555;

    private const ENLEM0 = 42.322;

    /**
     * İl adı => [enlem, boylam, etiket yeri]. Etiket yeri varsayılan "sag"; kalabalık bölgede elle verildi.
     *
     * @var array<string, array{0: float, 1: float, 2?: string}>
     */
    public const ILLER = [
        'Adana' => [37.00, 35.32],
        'Adıyaman' => [37.76, 38.28],
        'Afyonkarahisar' => [38.76, 30.54],
        'Ağrı' => [39.72, 43.05],
        'Aksaray' => [38.37, 34.03],
        'Amasya' => [40.65, 35.83],
        'Ankara' => [39.93, 32.86],
        'Antalya' => [36.90, 30.70, 'alt'],
        'Ardahan' => [41.11, 42.70],
        'Artvin' => [41.18, 41.82],
        'Aydın' => [37.85, 27.85, 'sol'],
        'Balıkesir' => [39.65, 27.88],
        'Bartın' => [41.64, 32.34, 'ust'],
        'Batman' => [37.89, 41.13],
        'Bayburt' => [40.26, 40.23],
        'Bilecik' => [40.14, 29.98],
        'Bingöl' => [38.88, 40.50],
        'Bitlis' => [38.40, 42.11],
        'Bolu' => [40.74, 31.61],
        'Burdur' => [37.72, 30.29],
        'Bursa' => [40.19, 29.06, 'sol'],
        'Çanakkale' => [40.15, 26.41, 'sol'],
        'Çankırı' => [40.60, 33.62],
        'Çorum' => [40.55, 34.95],
        'Denizli' => [37.78, 29.09],
        'Diyarbakır' => [37.91, 40.24],
        'Düzce' => [40.84, 31.16, 'ust'],
        'Edirne' => [41.68, 26.56, 'sol'],
        'Elazığ' => [38.67, 39.22],
        'Erzincan' => [39.75, 39.49],
        'Erzurum' => [39.90, 41.27],
        'Eskişehir' => [39.78, 30.52],
        'Gaziantep' => [37.07, 37.38],
        'Giresun' => [40.91, 38.39],
        'Gümüşhane' => [40.46, 39.48],
        'Hakkari' => [37.58, 43.74],
        'Hatay' => [36.20, 36.16, 'sol'],
        'Iğdır' => [39.92, 44.05, 'ust'],
        'Isparta' => [37.76, 30.55],
        'İstanbul' => [41.01, 28.98, 'ust-sol'],
        'İzmir' => [38.42, 27.14, 'sol'],
        'Kahramanmaraş' => [37.58, 36.94],
        'Karabük' => [41.20, 32.62],
        'Karaman' => [37.18, 33.22],
        'Kars' => [40.60, 43.10],
        'Kastamonu' => [41.38, 33.78],
        'Kayseri' => [38.73, 35.48],
        'Kırıkkale' => [39.85, 33.51],
        'Kırklareli' => [41.73, 27.22],
        'Kırşehir' => [39.15, 34.16],
        'Kilis' => [36.72, 37.12, 'alt'],
        'Kocaeli' => [40.77, 29.92, 'ust-sag'],
        'Konya' => [37.87, 32.48],
        'Kütahya' => [39.42, 29.98],
        'Malatya' => [38.35, 38.31],
        'Manisa' => [38.61, 27.43],
        'Mardin' => [37.31, 40.74],
        'Mersin' => [36.81, 34.64, 'alt'],
        'Muğla' => [37.22, 28.36],
        'Muş' => [38.75, 41.49],
        'Nevşehir' => [38.62, 34.71],
        'Niğde' => [37.97, 34.68],
        'Ordu' => [40.98, 37.88],
        'Osmaniye' => [37.07, 36.25],
        'Rize' => [41.02, 40.52],
        'Sakarya' => [40.78, 30.40],
        'Samsun' => [41.29, 36.33],
        'Siirt' => [37.93, 41.94],
        'Sinop' => [42.03, 35.15],
        'Sivas' => [39.75, 37.02],
        'Şanlıurfa' => [37.16, 38.79],
        'Şırnak' => [37.52, 42.46],
        'Tekirdağ' => [40.98, 27.51, 'sol'],
        'Tokat' => [40.31, 36.55],
        'Trabzon' => [41.00, 39.72],
        'Tunceli' => [39.11, 39.55],
        'Uşak' => [38.68, 29.41],
        'Van' => [38.49, 43.38],
        'Yalova' => [40.66, 29.28, 'alt'],
        'Yozgat' => [39.82, 34.81],
        'Zonguldak' => [41.45, 31.79, 'ust'],
    ];

    /**
     * Kaba sınır, saat yönünde (boylam, enlem). Şematik; kıyı ayrıntısı bilinçli olarak atlandı.
     *
     * @var list<array{0: float, 1: float}>
     */
    private const SINIR = [
        [26.04, 40.73], [26.36, 40.95], [26.60, 41.33], [26.33, 41.71], [26.62, 41.97], [27.20, 42.06],
        [28.00, 41.98], [28.10, 41.60], [28.62, 41.35], [29.10, 41.24], [29.70, 41.16], [30.40, 41.20],
        [31.40, 41.30], [31.80, 41.48], [32.40, 41.76], [33.30, 42.00], [34.20, 41.98], [34.95, 42.09],
        [35.20, 42.02], [35.50, 41.70], [36.05, 41.70], [36.40, 41.28], [37.00, 41.25], [37.60, 41.10],
        [38.40, 40.92], [39.10, 41.04], [39.75, 41.00], [40.55, 41.04], [41.45, 41.40], [41.55, 41.52],
        [42.50, 41.45], [42.85, 41.58], [43.47, 41.12], [43.72, 40.75], [43.60, 40.45], [43.70, 40.10],
        [44.40, 40.00], [44.82, 39.65], [44.42, 39.42], [44.30, 38.95], [44.25, 38.40], [44.45, 38.05],
        [44.60, 37.40], [44.79, 37.16], [44.10, 37.30], [43.40, 37.30], [42.80, 37.37], [42.36, 37.11],
        [41.60, 37.08], [41.20, 37.07], [40.20, 36.95], [39.20, 36.68], [38.20, 36.85], [37.40, 36.65],
        [36.70, 36.82], [36.58, 36.25], [36.30, 35.95], [36.05, 35.82], [35.92, 36.05], [35.90, 36.42],
        [36.17, 36.62], [36.10, 36.85], [35.78, 36.75], [35.55, 36.60], [35.35, 36.55], [34.95, 36.70],
        [34.60, 36.78], [34.20, 36.55], [33.90, 36.30], [33.40, 36.15], [32.80, 36.02], [32.30, 36.25],
        [31.95, 36.55], [31.40, 36.75], [30.75, 36.85], [30.55, 36.45], [30.40, 36.25], [29.95, 36.22],
        [29.60, 36.18], [29.30, 36.30], [29.10, 36.65], [28.70, 36.70], [28.30, 36.82], [27.37, 36.72],
        [28.00, 37.00], [27.45, 37.05], [27.25, 37.35], [27.25, 37.95], [26.80, 38.15], [26.30, 38.30],
        [26.45, 38.65], [26.75, 38.50], [27.05, 38.45], [26.80, 38.70], [26.75, 38.95], [26.85, 39.10],
        [26.65, 39.32], [26.95, 39.55], [26.35, 39.50], [26.07, 39.47], [26.15, 39.85], [26.20, 40.05],
        [26.18, 40.05], [26.30, 40.30], [26.60, 40.50], [26.85, 40.62], [26.40, 40.65],
    ];

    /** @var list<array{0: float, 1: float}> */
    private const MARMARA = [
        [26.25, 40.08], [26.40, 40.18], [26.68, 40.38], [27.00, 40.45], [27.30, 40.40], [27.75, 40.38],
        [27.97, 40.35], [28.40, 40.38], [28.90, 40.40], [29.10, 40.42], [29.00, 40.55], [29.30, 40.68],
        [29.92, 40.72], [29.92, 40.77], [29.35, 40.80], [29.05, 40.95], [28.98, 41.00], [28.60, 40.98],
        [28.20, 41.05], [27.50, 40.96], [27.05, 40.70], [26.70, 40.45], [26.35, 40.12],
    ];

    /** @var list<array{0: float, 1: float}> */
    private const BOGAZ = [[28.99, 41.01], [29.10, 41.23]];

    /**
     * Serbest yazılmış il adını tablodaki adla eşler: "istanbul", "ISTANBUL", " İstanbul " → "İstanbul".
     */
    public static function kanonik(?string $il): ?string
    {
        if ($il === null || trim($il) === '') {
            return null;
        }

        static $dizin = null;

        if ($dizin === null) {
            $dizin = [];
            foreach (array_keys(self::ILLER) as $ad) {
                $dizin[self::anahtar($ad)] = $ad;
            }
            // Eski ad ve yaygın yazımlar.
            $dizin[self::anahtar('Afyon')] = 'Afyonkarahisar';
            $dizin[self::anahtar('Maraş')] = 'Kahramanmaraş';
            $dizin[self::anahtar('Urfa')] = 'Şanlıurfa';
            $dizin[self::anahtar('İçel')] = 'Mersin';
            $dizin[self::anahtar('Antep')] = 'Gaziantep';
            $dizin[self::anahtar('Izmit')] = 'Kocaeli';
            $dizin[self::anahtar('Adapazarı')] = 'Sakarya';
        }

        return $dizin[self::anahtar($il)] ?? null;
    }

    /**
     * İlin çizim alanındaki noktası ve etiket yeri.
     *
     * @return array{x: float, y: float, etiket: string}|null
     */
    public static function nokta(string $il): ?array
    {
        $ad = self::kanonik($il);

        if ($ad === null) {
            return null;
        }

        $kayit = self::ILLER[$ad];
        [$x, $y] = self::izdusum($kayit[1], $kayit[0]);

        return ['x' => $x, 'y' => $y, 'etiket' => $kayit[2] ?? 'sag'];
    }

    /** @return array{0: float, 1: float} */
    public static function izdusum(float $boylam, float $enlem): array
    {
        return [
            round(($boylam - self::BOYLAM0) * self::KX, 1),
            round((self::ENLEM0 - $enlem) * self::KY, 1),
        ];
    }

    public static function sinirNoktalari(): string
    {
        return self::noktaDizisi(self::SINIR);
    }

    public static function marmaraNoktalari(): string
    {
        return self::noktaDizisi(self::MARMARA);
    }

    /** @return array{x1: float, y1: float, x2: float, y2: float} */
    public static function bogaz(): array
    {
        [$x1, $y1] = self::izdusum(...self::BOGAZ[0]);
        [$x2, $y2] = self::izdusum(...self::BOGAZ[1]);

        return compact('x1', 'y1', 'x2', 'y2');
    }

    /**
     * Arka plan ızgarası: her 1° enlem ve her 2° boylam.
     *
     * @return array{yatay: list<float>, dikey: list<float>}
     */
    public static function izgara(): array
    {
        $yatay = [];
        for ($enlem = 42; $enlem >= 36; $enlem--) {
            $yatay[] = self::izdusum(26, $enlem)[1];
        }

        $dikey = [];
        for ($boylam = 26; $boylam <= 44; $boylam += 2) {
            $dikey[] = self::izdusum($boylam, 40)[0];
        }

        return ['yatay' => $yatay, 'dikey' => $dikey];
    }

    /** @param  list<array{0: float, 1: float}>  $noktalar */
    private static function noktaDizisi(array $noktalar): string
    {
        return implode(' ', array_map(function (array $n): string {
            [$x, $y] = self::izdusum($n[0], $n[1]);

            return $x.','.$y;
        }, $noktalar));
    }

    private static function anahtar(string $metin): string
    {
        $metin = str_replace(['İ', 'I'], ['i', 'ı'], trim($metin));

        return Str::slug(Str::ascii(mb_strtolower($metin, 'UTF-8'), 'tr'));
    }
}
