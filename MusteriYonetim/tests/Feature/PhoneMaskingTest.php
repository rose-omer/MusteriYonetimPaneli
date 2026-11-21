<?php

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use App\Models\CustomerAudit;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->customer = Customer::factory()->create(['telefon_numarasi' => '05321234567']);
});

it('izleyici telefonu maskeli görür', function () {
    actingAsRole('viewer');

    livewire(ListCustomers::class)
        ->assertSee('0532 *** ** 67')
        ->assertDontSee('0532 123 45 67');

    livewire(ViewCustomer::class, ['record' => $this->customer->getRouteKey()])
        ->assertSee('0532 *** ** 67')
        ->assertDontSee('123 45 67');
});

it('admin ve düzenleyici tam numarayı görür', function (string $role) {
    actingAsRole($role);

    livewire(ListCustomers::class)->assertSee('0532 123 45 67');
})->with(['admin', 'editor']);

it('izleyici telefon numarasıyla arayamaz', function () {
    actingAsRole('viewer');

    livewire(ListCustomers::class)
        ->searchTable('1234567')
        ->assertCanNotSeeTableRecords([$this->customer])
        ->assertSee('Unvan ya da firma no ara');
});

it('düzenleyici telefon numarasıyla arar', function () {
    actingAsRole('editor');

    livewire(ListCustomers::class)
        ->searchTable('1234567')
        ->assertCanSeeTableRecords([$this->customer]);
});

it('denetim kaydına telefonu yalnız maskeli yazar', function () {
    actingAsRole('editor');

    $this->customer->update(['telefon_numarasi' => '05329876543']);

    $change = CustomerAudit::where('action', 'updated')->sole()->changed_fields['telefon_numarasi'];

    expect($change)->toBe(['old' => '0532 *** ** 67', 'new' => '0532 *** ** 43'])
        ->and(json_encode(CustomerAudit::pluck('changed_fields')))->not->toContain('9876543');
});

it('liste satırı müşteri ayrıntısına bağlanır', function () {
    actingAsRole('viewer');

    $table = livewire(ListCustomers::class)->instance()->getTable();

    expect($table->getRecordUrl($this->customer))->toBe(CustomerResource::getUrl('view', ['record' => $this->customer]));
});
