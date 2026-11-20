<?php

namespace Database\Factories;

use App\Enums\FaturaDurumu;
use App\Enums\FaturaTipi;
use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Models\Customer;
use App\Models\Faaliyet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Yalnız sahte veri üretir (Faker tr_TR). Gerçek müşteri verisi kullanılmaz.
 *
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /** @var list<string> */
    private const ILLER = ['İstanbul', 'Ankara', 'İzmir', 'Bursa', 'Kocaeli', 'Antalya', 'Konya', 'Gaziantep', 'Kayseri', 'Eskişehir', 'Sakarya', 'Manisa', 'Denizli', 'Tekirdağ', 'Mersin'];

    /** @var list<string> */
    private const UNVAN_EKLERI = ['Ltd. Şti.', 'A.Ş.', 'San. ve Tic. Ltd. Şti.', 'İnşaat A.Ş.', 'Gıda San. A.Ş.', 'Lojistik Ltd. Şti.'];

    public function definition(): array
    {
        $faker = fake('tr_TR');
        $ad = $faker->lastName().' '.$faker->randomElement(['Metal', 'Tekstil', 'Plastik', 'Mobilya', 'Otomotiv', 'Ambalaj', 'Kimya', 'Yapı', 'Enerji', 'Tarım']);

        return [
            'firma_no' => (string) $faker->unique()->numberBetween(10000, 99999),
            'hizmet_alan_isyeri_unvani' => $ad.' '.$faker->randomElement(self::UNVAN_EKLERI),
            'gorunur_unvani' => $ad,
            'faaliyet_id' => null,
            'calisan_sayisi' => $faker->numberBetween(3, 450),
            'tehlike_sinifi' => $faker->randomElement(TehlikeSinifi::cases()),
            'firma_sorumlusu' => $faker->name(),
            'telefon_numarasi' => '05'.$faker->numerify('#########'),
            'il' => $faker->randomElement(self::ILLER),
            'mahalle' => $faker->randomElement(['Atatürk', 'Cumhuriyet', 'Yıldız', 'Fatih', 'Merkez', 'Organize Sanayi', 'Yeni']).' Mah.',
            'konum_url' => 'https://maps.google.com/?q='.$faker->latitude(36, 42).','.$faker->longitude(26, 44),
            'fatura_tipi' => $faker->randomElement(FaturaTipi::cases()),
            'fatura_durumu' => $faker->randomElement(FaturaDurumu::cases()),
            'tutar' => $faker->randomFloat(2, 1500, 85000),
            'odeme_durumu' => $faker->randomElement([OdemeDurumu::Odenmis, OdemeDurumu::Odenmis, OdemeDurumu::KismiOdenmis, OdemeDurumu::Odenmemis]),
            'sozlesme_onay_durumu' => $faker->boolean(75),
            'igu' => $faker->name(),
            'ih' => 'Dr. '.$faker->name(),
            'firma_ziyareti' => $faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'defter' => $faker->randomElement(['Var', 'Yok', 'Teslim edildi']),
            'ra' => $faker->randomElement(['Hazır', 'Güncellenecek', 'Yok']),
        ];
    }

    public function withFaaliyet(): static
    {
        return $this->state(fn () => ['faaliyet_id' => Faaliyet::factory()]);
    }

    public function odenmemis(): static
    {
        return $this->state(fn () => ['odeme_durumu' => OdemeDurumu::Odenmemis]);
    }
}
