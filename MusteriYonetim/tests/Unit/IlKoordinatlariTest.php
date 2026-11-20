<?php

use App\Support\IlKoordinatlari;

it('81 ilin hepsi çizim alanının içinde', function () {
    expect(IlKoordinatlari::ILLER)->toHaveCount(81);

    foreach (array_keys(IlKoordinatlari::ILLER) as $il) {
        $nokta = IlKoordinatlari::nokta($il);

        expect($nokta)->not->toBeNull()
            ->and($nokta['x'])->toBeGreaterThan(0)->toBeLessThan(IlKoordinatlari::GENISLIK)
            ->and($nokta['y'])->toBeGreaterThan(0)->toBeLessThan(IlKoordinatlari::YUKSEKLIK);
    }
});

it('il adını yazım farkından bağımsız eşler', function (string $girdi, ?string $beklenen) {
    expect(IlKoordinatlari::kanonik($girdi))->toBe($beklenen);
})->with([
    ['İstanbul', 'İstanbul'],
    ['ISTANBUL', 'İstanbul'],
    ['istanbul ', 'İstanbul'],
    ['IĞDIR', 'Iğdır'],
    ['sanliurfa', 'Şanlıurfa'],
    ['Urfa', 'Şanlıurfa'],
    ['İçel', 'Mersin'],
    ['Atlantis', null],
    ['', null],
]);
