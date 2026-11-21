<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Faaliyet;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo verisi. Bütün kayıtlar sahtedir.
 * Demo hesapları (parola: "password"): admin@demo.test, editor@demo.test, izleyici@demo.test.
 */
class DatabaseSeeder extends Seeder
{
    /** @var list<string> */
    private const FAALIYETLER = [
        'Metal işleme', 'Tekstil üretimi', 'Gıda üretimi', 'İnşaat', 'Lojistik ve depolama',
        'Plastik üretimi', 'Mobilya üretimi', 'Otomotiv yan sanayi', 'Kimya', 'Perakende',
        'Ofis hizmetleri', 'Sağlık hizmetleri',
    ];

    public function run(): void
    {
        User::factory()->admin()->create(['name' => 'Demo Yönetici', 'email' => 'admin@demo.test']);
        User::factory()->editor()->create(['name' => 'Demo Düzenleyici', 'email' => 'editor@demo.test']);
        User::factory()->viewer()->create(['name' => 'Demo İzleyici', 'email' => 'izleyici@demo.test']);

        $faaliyetIds = collect(self::FAALIYETLER)
            ->map(fn (string $isim) => Faaliyet::create(['isim' => $isim])->id);

        Customer::withoutEvents(function () use ($faaliyetIds) {
            Customer::factory()
                ->count(200)
                ->sequence(fn () => [
                    'faaliyet_id' => $faaliyetIds->random(),
                    'created_at' => now()->subDays(random_int(0, 240)),
                ])
                ->create();
        });
    }
}
