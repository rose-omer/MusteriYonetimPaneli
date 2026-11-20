<?php

namespace Database\Factories;

use App\Models\Faaliyet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faaliyet>
 */
class FaaliyetFactory extends Factory
{
    protected $model = Faaliyet::class;

    public function definition(): array
    {
        return [
            'isim' => fake('tr_TR')->randomElement([
                'Metal işleme', 'Tekstil üretimi', 'Gıda üretimi', 'İnşaat', 'Lojistik ve depolama',
                'Plastik üretimi', 'Mobilya üretimi', 'Otomotiv yan sanayi', 'Kimya', 'Perakende',
                'Ofis hizmetleri', 'Sağlık hizmetleri', 'Eğitim', 'Tarım', 'Enerji',
            ]).' '.fake()->unique()->numberBetween(1, 99999),
        ];
    }
}
