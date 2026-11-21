<?php

use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Filament\Pages\MusteriHaritasi;
use App\Models\Customer;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->bursa = Customer::factory()->create([
        'hizmet_alan_isyeri_unvani' => 'Bursa Oto Yedek Parça Ltd. Şti.',
        'il' => 'Bursa',
        'tehlike_sinifi' => TehlikeSinifi::Tehlikeli,
        'tutar' => 41300,
        'odeme_durumu' => OdemeDurumu::Odenmemis,
        'telefon_numarasi' => '05324182247',
    ]);
    $this->ankara = Customer::factory()->create([
        'hizmet_alan_isyeri_unvani' => 'Yıldız Yapı İnşaat Ltd. Şti.',
        'il' => 'ankara',
        'tehlike_sinifi' => TehlikeSinifi::CokTehlikeli,
        'tutar' => 31200,
        'odeme_durumu' => OdemeDurumu::Odenmis,
    ]);
});

it('harita sayfası açılır ve illeri daire olarak çizer', function () {
    actingAsRole('viewer');

    $this->get(MusteriHaritasi::getUrl())
        ->assertOk()
        ->assertSee('Müşteri haritası')
        ->assertSee("Bursa: 1 müşteri, açık tutar 41.300\u{00A0}₺, çoğunluk tehlikeli", false)
        ->assertSee('Ankara: 1 müşteri', false)
        ->assertSee('Firma seçilmedi');
});

it('ile basınca liste o ile süzülür, ikinci basış süzgeci kaldırır', function () {
    actingAsRole('viewer');

    livewire(MusteriHaritasi::class)
        ->call('ilSec', 'Bursa')
        ->assertSet('il', 'Bursa')
        ->assertSee('Bursa Oto Yedek Parça')
        ->assertDontSee('Yıldız Yapı İnşaat')
        ->call('ilSec', 'Bursa')
        ->assertSet('il', '')
        ->assertSee('Yıldız Yapı İnşaat');
});

it('farklı yazılmış il adı aynı ile eşlenir', function () {
    actingAsRole('viewer');

    livewire(MusteriHaritasi::class)
        ->call('ilSec', 'Ankara')
        ->assertSee('Yıldız Yapı İnşaat')
        ->assertDontSee('Bursa Oto Yedek Parça');
});

it('tehlike sınıfı ve ödeme durumu süzgeci çalışır', function () {
    actingAsRole('viewer');

    livewire(MusteriHaritasi::class)
        ->call('tehlikeSec', 'cok_tehlikeli')
        ->assertSee('Yıldız Yapı İnşaat')
        ->assertDontSee('Bursa Oto Yedek Parça')
        ->call('tehlikeSec', '')
        ->set('odeme', 'acik')
        ->assertSee('Bursa Oto Yedek Parça')
        ->assertDontSee('Yıldız Yapı İnşaat');
});

it('seçilen firmanın künyesini gösterir; izleyici telefonu maskeli görür', function () {
    actingAsRole('viewer');

    livewire(MusteriHaritasi::class)
        ->call('sec', $this->bursa->id)
        ->assertSet('secili', $this->bursa->id)
        ->assertSee('Firma sorumlusu')
        ->assertSee('0532 *** ** 47')
        ->assertDontSee('0532 418 22 47')
        ->assertSee("41.300,00\u{00A0}₺")
        ->call('kapat')
        ->assertSee('Firma seçilmedi');
});

it('düzenleyici tam numarayı ve düzenle bağlantısını görür', function () {
    actingAsRole('editor');

    livewire(MusteriHaritasi::class)
        ->call('sec', $this->bursa->id)
        ->assertSee('0532 418 22 47')
        ->assertSee('Düzenle');
});

it('izleyici telefonla arayamaz, düzenleyici arar', function () {
    actingAsRole('viewer');
    livewire(MusteriHaritasi::class)
        ->set('arama', '4182247')
        ->assertSee('Bu ölçütlere uyan müşteri yok');

    actingAsRole('editor');
    livewire(MusteriHaritasi::class)
        ->set('arama', '4182247')
        ->assertSee('Bursa Oto Yedek Parça');
});

it('izleyici toplu işlem göremez', function () {
    actingAsRole('viewer');

    livewire(MusteriHaritasi::class)
        ->assertActionHidden('odeme')
        ->assertActionHidden('sil')
        ->assertDontSeeHtml('wire:model.live="isaretli"');
});

it('düzenleyici seçilenlerin ödeme durumunu toplu değiştirir', function () {
    actingAsRole('editor');

    livewire(MusteriHaritasi::class)
        ->set('isaretli', [(string) $this->bursa->id, (string) $this->ankara->id])
        ->assertSee('2 seçili')
        ->callAction('odeme', data: ['value' => OdemeDurumu::KismiOdenmis->value])
        ->assertHasNoActionErrors()
        ->assertSet('isaretli', []);

    expect($this->bursa->fresh()->odeme_durumu)->toBe(OdemeDurumu::KismiOdenmis)
        ->and($this->ankara->fresh()->odeme_durumu)->toBe(OdemeDurumu::KismiOdenmis);
});

it('yönetici seçilenleri siler; düzenleyici silemez', function () {
    actingAsRole('editor');
    livewire(MusteriHaritasi::class)->assertActionHidden('sil');

    actingAsRole('admin');
    livewire(MusteriHaritasi::class)
        ->set('isaretli', [(string) $this->bursa->id])
        ->callAction('sil');

    expect($this->bursa->fresh()->trashed())->toBeTrue()
        ->and($this->ankara->fresh()->trashed())->toBeFalse();
});

it('tanınmayan il haritada sayılır ama çizilmez', function () {
    actingAsRole('viewer');
    Customer::factory()->create(['il' => 'Atlantis']);

    livewire(MusteriHaritasi::class)->assertSee('1 müşterinin ili tanınmadı');
});

it('boş veritabanında boş durum gösterir', function () {
    Customer::query()->forceDelete();
    actingAsRole('admin');

    livewire(MusteriHaritasi::class)
        ->assertSee('Henüz müşteri yok')
        ->assertSee("Excel'den içe aktar", false);
});

it('yakın illerde etiket komşu daireye binmez', function () {
    actingAsRole('viewer');

    Customer::factory()->create(['il' => 'Kocaeli', 'tutar' => 9000, 'odeme_durumu' => OdemeDurumu::Odenmemis]);
    Customer::factory()->create(['il' => 'Sakarya', 'tutar' => 60000, 'odeme_durumu' => OdemeDurumu::Odenmemis]);

    $iller = collect(livewire(MusteriHaritasi::class)->instance()->harita['iller'])->keyBy('ad');
    $kocaeli = $iller['Kocaeli']['etiket'];
    $sakarya = $iller['Sakarya'];

    // Etiket kutusu (13 px yazı) ile Sakarya dairesi arasındaki en kısa uzaklık yarıçaptan büyük.
    $genislik = mb_strlen('Kocaeli') * 7.0;
    $x0 = match ($kocaeli['yon']) {
        'end' => $kocaeli['x'] - $genislik, 'middle' => $kocaeli['x'] - $genislik / 2, default => $kocaeli['x']
    };
    $nx = max($x0, min($sakarya['x'], $x0 + $genislik));
    $ny = max($kocaeli['y'] - 10, min($sakarya['y'], $kocaeli['y'] + 3));

    expect(hypot($nx - $sakarya['x'], $ny - $sakarya['y']))->toBeGreaterThan($sakarya['r']);
});
