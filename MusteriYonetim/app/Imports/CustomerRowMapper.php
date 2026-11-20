<?php

namespace App\Imports;

use App\Enums\FaturaDurumu;
use App\Enums\FaturaTipi;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Models\Faaliyet;
use App\Support\TurkishFormat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Excel satırı ↔ müşteri alanları eşlemesi. İçe aktarım, önizleme, şablon ve
 * dışa aktarım aynı sütun listesini kullanır.
 */
final class CustomerRowMapper
{
    /**
     * Excel başlığı (2. satır) → müşteri alanı. Anahtar, Laravel Excel'in
     * başlıktan ürettiği "slug" biçimidir.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const COLUMNS = [
        'firma_no' => ['firma_no', 'Firma No'],
        'hizmet_alan_isyeri_unvani' => ['hizmet_alan_isyeri_unvani', 'Hizmet Alan İşyeri Unvanı'],
        'gorunur_unvani' => ['gorunur_unvani', 'Görünür Unvanı'],
        'faaliyet' => ['faaliyet', 'Faaliyet'],
        'hizmet_alan_isyeri_calisan_sayisi' => ['calisan_sayisi', 'Hizmet Alan İşyeri Çalışan Sayısı'],
        'hizmet_alan_isyeri_tehlike_sinifi' => ['tehlike_sinifi', 'Hizmet Alan İşyeri Tehlike Sınıfı'],
        'firma_sorumlusu' => ['firma_sorumlusu', 'Firma Sorumlusu'],
        'yetkili_kisi_telefon_numarasi' => ['telefon_numarasi', 'Yetkili Kişi Telefon Numarası'],
        'hizmet_alan_isyeri_ili' => ['il', 'Hizmet Alan İşyeri İli'],
        'mahalle' => ['mahalle', 'Mahalle'],
        'konum' => ['konum_url', 'Konum'],
        'fatura_tipi' => ['fatura_tipi', 'Fatura Tipi'],
        'fatura_durumu' => ['fatura_durumu', 'Fatura Durumu'],
        'tutar' => ['tutar', 'Tutar'],
        'odeme' => ['odeme_durumu', 'Ödeme'],
        'sozlesme_onay_durumu' => ['sozlesme_onay_durumu', 'Sözleşme Onay Durumu'],
        'igu' => ['igu', 'İGU'],
        'ih' => ['ih', 'İH'],
        'firma_ziyareti' => ['firma_ziyareti', 'Firma Ziyareti'],
        'defter' => ['defter', 'Defter'],
        'ra' => ['ra', 'RA'],
    ];

    /** @var array<string, string> Excel'deki alan adıyla ("telefon_numarasi") gelen başlıklar da kabul edilir. */
    private const ATTRIBUTE_ALIASES = [
        'calisan_sayisi' => 'hizmet_alan_isyeri_calisan_sayisi',
        'tehlike_sinifi' => 'hizmet_alan_isyeri_tehlike_sinifi',
        'telefon_numarasi' => 'yetkili_kisi_telefon_numarasi',
        'il' => 'hizmet_alan_isyeri_ili',
        'konum_url' => 'konum',
        'odeme_durumu' => 'odeme',
    ];

    /**
     * Ham Excel satırını doğrulanacak alanlara çevirir. Çözülemeyen değer ham
     * hâliyle kalır; doğrulama onu satır hatası olarak raporlar.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function map(array $row): array
    {
        foreach (self::ATTRIBUTE_ALIASES as $alias => $key) {
            if (! array_key_exists($key, $row) && array_key_exists($alias, $row)) {
                $row[$key] = $row[$alias];
            }
        }

        $data = [];
        foreach (self::COLUMNS as $key => [$attribute]) {
            $value = $row[$key] ?? null;
            $data[$attribute] = is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value;
        }

        $data['firma_no'] = $data['firma_no'] === null ? null : (string) $data['firma_no'];
        $data['telefon_numarasi'] = $data['telefon_numarasi'] === null ? null : (string) $data['telefon_numarasi'];
        $data['tutar'] = TurkishFormat::money($data['tutar']) ?? $data['tutar'];
        $data['firma_ziyareti'] = TurkishFormat::date($data['firma_ziyareti']) ?? $data['firma_ziyareti'];

        $onay = TurkishFormat::bool($data['sozlesme_onay_durumu']);
        $data['sozlesme_onay_durumu'] = $data['sozlesme_onay_durumu'] === null ? false : ($onay ?? $data['sozlesme_onay_durumu']);

        foreach ([
            'tehlike_sinifi' => TehlikeSinifi::class,
            'fatura_tipi' => FaturaTipi::class,
            'fatura_durumu' => FaturaDurumu::class,
            'odeme_durumu' => OdemeDurumu::class,
        ] as $attribute => $enum) {
            if ($data[$attribute] !== null) {
                $data[$attribute] = $enum::fromLabel($data[$attribute])->value ?? $data[$attribute];
            }
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'firma_no' => ['nullable', 'string', 'max:50'],
            'hizmet_alan_isyeri_unvani' => ['required', 'string', 'max:255'],
            'gorunur_unvani' => ['nullable', 'string', 'max:255'],
            'faaliyet' => ['nullable', 'string', 'max:255'],
            'calisan_sayisi' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'tehlike_sinifi' => ['nullable', Rule::enum(TehlikeSinifi::class)],
            'firma_sorumlusu' => ['nullable', 'string', 'max:255'],
            'telefon_numarasi' => ['nullable', 'string', 'max:30', 'regex:/^[\d\s()+-]+$/'],
            'il' => ['nullable', 'string', 'max:100'],
            'mahalle' => ['nullable', 'string', 'max:255'],
            'konum_url' => ['nullable', 'url:https', 'max:2048'],
            'fatura_tipi' => ['nullable', Rule::enum(FaturaTipi::class)],
            'fatura_durumu' => ['nullable', Rule::enum(FaturaDurumu::class)],
            'tutar' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'odeme_durumu' => ['nullable', Rule::enum(OdemeDurumu::class)],
            'sozlesme_onay_durumu' => ['boolean'],
            'igu' => ['nullable', 'string', 'max:255'],
            'ih' => ['nullable', 'string', 'max:255'],
            'firma_ziyareti' => ['nullable', 'date_format:Y-m-d'],
            'defter' => ['nullable', 'string', 'max:255'],
            'ra' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> Hata mesajlarında Excel başlığı görünür. */
    public static function attributeLabels(): array
    {
        $labels = [];
        foreach (self::COLUMNS as [$attribute, $label]) {
            $labels[$attribute] = $label;
        }

        return $labels;
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [
            'tutar.numeric' => 'Tutar sayı değil.',
            'firma_ziyareti.date_format' => 'Firma ziyareti tarih değil (örnek: 15.03.2025).',
            'konum_url.url' => 'Konum https:// ile başlayan bir bağlantı olmalı.',
            'sozlesme_onay_durumu.boolean' => 'Sözleşme onayı "Evet" ya da "Hayır" olmalı.',
            'telefon_numarasi.regex' => 'Telefon numarası yalnız rakam, boşluk ve + ( ) - içerebilir.',
        ];
    }

    /**
     * Tek satırı doğrular; hata mesajlarını döndürür.
     *
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public static function errors(array $data): array
    {
        $validator = Validator::make($data, self::rules(), self::messages(), self::attributeLabels());

        return array_values($validator->errors()->all());
    }

    /** Faaliyet adını kimliğe çevirir; yoksa oluşturur. */
    public static function faaliyetId(?string $isim): ?int
    {
        if ($isim === null || trim($isim) === '') {
            return null;
        }

        return Faaliyet::firstOrCreate(['isim' => trim($isim)])->id;
    }
}
