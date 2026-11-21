<?php

use App\Filament\Resources\Faaliyets\Pages\ManageFaaliyets;
use App\Models\Customer;
use App\Models\Faaliyet;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('yeniden adlandırma müşteri bağını korur', function () {
    actingAsRole('admin');
    $faaliyet = Faaliyet::create(['isim' => 'Metal']);
    $customers = Customer::factory()->count(3)->create(['faaliyet_id' => $faaliyet->id]);

    livewire(ManageFaaliyets::class)
        ->callAction(TestAction::make(EditAction::class)->table($faaliyet), data: ['isim' => 'Metal işleme'])
        ->assertHasNoFormErrors();

    expect($faaliyet->fresh()->isim)->toBe('Metal işleme')
        ->and(Customer::where('faaliyet_id', $faaliyet->id)->count())->toBe(3)
        ->and($customers->first()->fresh()->faaliyet->isim)->toBe('Metal işleme');
});

it('silmeden önce bağlı müşteri sayısını söyler, silince bağ boşalır', function () {
    actingAsRole('admin');
    $faaliyet = Faaliyet::create(['isim' => 'Kimya']);
    $customer = Customer::factory()->create(['faaliyet_id' => $faaliyet->id]);

    livewire(ManageFaaliyets::class)
        ->mountAction(TestAction::make(DeleteAction::class)->table($faaliyet))
        ->assertMountedActionModalSee('Bu faaliyete bağlı 1 müşteri var')
        ->callMountedAction();

    expect(Faaliyet::count())->toBe(0)
        ->and($customer->fresh()->faaliyet_id)->toBeNull();
});

it('aynı adla ikinci faaliyet eklenmez', function () {
    actingAsRole('admin');
    Faaliyet::create(['isim' => 'Gıda']);

    livewire(ManageFaaliyets::class)
        ->callAction('create', data: ['isim' => 'Gıda'])
        ->assertHasFormErrors(['isim' => 'unique']);
});

it('izleyici faaliyet ekleyemez', function () {
    actingAsRole('viewer');

    livewire(ManageFaaliyets::class)->assertActionHidden('create');
});
