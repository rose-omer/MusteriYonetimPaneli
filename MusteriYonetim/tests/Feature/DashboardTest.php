<?php

use App\Enums\OdemeDurumu;
use App\Filament\Widgets\CustomerStatsOverview;
use App\Filament\Widgets\GecikmisOdemeler;
use App\Filament\Widgets\IlChart;
use App\Filament\Widgets\TehlikeSinifiChart;
use App\Models\Customer;

use function Pest\Livewire\livewire;

beforeEach(fn () => actingAsRole('viewer'));

it('boş veritabanında boş durum mesajlarını gösterir', function () {
    livewire(CustomerStatsOverview::class)->assertSee('Henüz müşteri yok');
    livewire(GecikmisOdemeler::class)->assertSee('Gecikmiş ödeme yok');
    livewire(TehlikeSinifiChart::class)->assertSee('Henüz veri yok');
    livewire(IlChart::class)->assertSee('Henüz veri yok');
});

it('demo verisinde bütün widgetlar dolu', function () {
    Customer::factory()->create(['tutar' => 1000, 'odeme_durumu' => OdemeDurumu::Odenmemis, 'il' => 'Bursa', 'sozlesme_onay_durumu' => false]);
    Customer::factory()->create(['tutar' => 500, 'odeme_durumu' => OdemeDurumu::Odenmis, 'il' => 'Ankara']);

    livewire(CustomerStatsOverview::class)
        ->assertSee('Toplam müşteri')
        ->assertSee('1 firmada ödeme bekliyor');

    livewire(GecikmisOdemeler::class)->assertCanSeeTableRecords(Customer::where('odeme_durumu', 'odenmemis')->get());
    livewire(IlChart::class)->assertDontSee('Henüz veri yok');
});

it('gösterge paneli açılır', function () {
    $this->get('/admin')->assertOk()->assertSee('Gösterge paneli');
});

it('istatistik kartları dar alanda iki sütuna iner', function () {
    Customer::factory()->count(3)->create();

    livewire(CustomerStatsOverview::class)
        ->assertSeeHtml('@xl:fi-grid-cols')
        ->assertSeeHtml('@5xl:fi-grid-cols')
        ->assertSeeHtml('--cols-c5xl: repeat(4, minmax(0, 1fr))');
});
