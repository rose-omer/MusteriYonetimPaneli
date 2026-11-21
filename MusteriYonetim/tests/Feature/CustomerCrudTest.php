<?php

use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\CustomerAudit;
use App\Models\Faaliyet;

use function Pest\Livewire\livewire;

beforeEach(fn () => actingAsRole('editor'));

function validCustomerData(array $overrides = []): array
{
    return [
        'hizmet_alan_isyeri_unvani' => 'Deneme Metal San. Ltd. Şti.',
        'firma_no' => '50001',
        'faaliyet_id' => Faaliyet::factory()->create()->id,
        'tehlike_sinifi' => TehlikeSinifi::CokTehlikeli->value,
        'telefon_numarasi' => '0532 123 45 67',
        'il' => 'Bursa',
        'konum_url' => 'https://maps.google.com/?q=40,29',
        'fatura_tipi' => 'aylik',
        'fatura_durumu' => 'kesildi',
        'tutar' => '1250.50',
        'odeme_durumu' => OdemeDurumu::Odenmemis->value,
        'sozlesme_onay_durumu' => true,
        'firma_ziyareti' => '2025-03-15',
        ...$overrides,
    ];
}

it('geçerli veriyle müşteri oluşturur ve denetim kaydı yazar', function () {
    livewire(CreateCustomer::class)
        ->fillForm(validCustomerData())
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $customer = Customer::firstOrFail();

    expect($customer->tehlike_sinifi)->toBe(TehlikeSinifi::CokTehlikeli)
        ->and($customer->odeme_durumu)->toBe(OdemeDurumu::Odenmemis)
        ->and($customer->tutar)->toBe('1250.50')
        ->and($customer->sozlesme_onay_durumu)->toBeTrue()
        ->and($customer->firma_ziyareti->format('Y-m-d'))->toBe('2025-03-15')
        ->and(CustomerAudit::where('customer_id', $customer->id)->where('action', 'created')->exists())->toBeTrue();
});

it('geçersiz değeri reddeder', function (array $override, string $field) {
    livewire(CreateCustomer::class)
        ->fillForm(validCustomerData($override))
        ->call('create')
        ->assertHasFormErrors([$field]);

    expect(Customer::count())->toBe(0);
})->with([
    'enum dışı fatura tipi' => [['fatura_tipi' => 'HAFTALIK_X'], 'fatura_tipi'],
    'javascript bağlantısı' => [['konum_url' => 'javascript:alert(1)'], 'konum_url'],
    'http bağlantısı' => [['konum_url' => 'http://example.com'], 'konum_url'],
    'negatif tutar' => [['tutar' => '-5'], 'tutar'],
    'boş unvan' => [['hizmet_alan_isyeri_unvani' => ''], 'hizmet_alan_isyeri_unvani'],
    'harf içeren telefon' => [['telefon_numarasi' => '0532 abc'], 'telefon_numarasi'],
]);

it('aynı firma numarasını ikinci kez kabul etmez', function () {
    Customer::factory()->create(['firma_no' => '50001']);

    livewire(CreateCustomer::class)
        ->fillForm(validCustomerData())
        ->call('create')
        ->assertHasFormErrors(['firma_no' => 'unique']);
});

it('güncellemede değişen alanları denetim kaydına yazar', function () {
    $customer = Customer::factory()->create(['tutar' => 100, 'odeme_durumu' => OdemeDurumu::Odenmemis]);

    livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])
        ->fillForm(['odeme_durumu' => OdemeDurumu::Odenmis->value])
        ->call('save')
        ->assertHasNoFormErrors();

    $audit = CustomerAudit::where('action', 'updated')->firstOrFail();

    expect($audit->changed_fields)->toHaveKey('odeme_durumu')
        ->and($audit->changed_fields['odeme_durumu'])->toBe(['old' => 'odenmemis', 'new' => 'odenmis'])
        ->and($audit->changeLines()[0]['new'])->toBe('Ödendi');
});

it('listede ödeme durumuna göre süzer', function () {
    $odenmemis = Customer::factory()->count(2)->create(['odeme_durumu' => OdemeDurumu::Odenmemis]);
    $odenmis = Customer::factory()->count(2)->create(['odeme_durumu' => OdemeDurumu::Odenmis]);

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($odenmemis->merge($odenmis))
        ->filterTable('odeme_durumu', [OdemeDurumu::Odenmemis->value])
        ->assertCanSeeTableRecords($odenmemis)
        ->assertCanNotSeeTableRecords($odenmis);
});

it('unvana göre arar', function () {
    $hedef = Customer::factory()->create(['hizmet_alan_isyeri_unvani' => 'Zümrüt Tekstil A.Ş.']);
    $diger = Customer::factory()->create(['hizmet_alan_isyeri_unvani' => 'Başka Firma Ltd.']);

    livewire(ListCustomers::class)
        ->searchTable('Zümrüt')
        ->assertCanSeeTableRecords([$hedef])
        ->assertCanNotSeeTableRecords([$diger]);
});

it('toplu işlemle ödeme durumunu değiştirir', function () {
    $customers = Customer::factory()->count(3)->create(['odeme_durumu' => OdemeDurumu::Odenmemis]);

    livewire(ListCustomers::class)
        ->callTableBulkAction('odeme_durumuDegistir', $customers, data: ['value' => OdemeDurumu::Odenmis->value])
        ->assertHasNoTableBulkActionErrors();

    expect(Customer::where('odeme_durumu', OdemeDurumu::Odenmis)->count())->toBe(3)
        ->and(CustomerAudit::where('action', 'updated')->count())->toBe(3);
});

it('boş listede ekleme ve içe aktarma yönlendirmesini gösterir', function () {
    livewire(ListCustomers::class)
        ->assertSee('Henüz müşteri yok')
        ->assertSee('Müşteri ekle');
});

it('silinen müşteriyi 90 gün sonra kalıcı siler', function () {
    $eski = Customer::factory()->create();
    $yeni = Customer::factory()->create();
    $eski->delete();
    $yeni->delete();
    Customer::withTrashed()->whereKey($eski->id)->update(['deleted_at' => now()->subDays(Customer::SAKLAMA_GUNU + 1)]);

    $this->artisan('model:prune', ['--model' => [Customer::class]])->assertSuccessful();

    expect(Customer::withTrashed()->find($eski->id))->toBeNull()
        ->and(Customer::withTrashed()->find($yeni->id))->not->toBeNull()
        // Denetim kaydı kalır, müşteri bağı boşalır.
        ->and(CustomerAudit::where('action', 'deleted')->count())->toBe(2);
});

it('süresi dolan ve sahipsiz denetim kayıtlarını siler', function () {
    $musteri = Customer::factory()->create();
    $eski = CustomerAudit::record('updated', $musteri, ['il' => ['old' => 'Bursa', 'new' => 'İzmir']]);
    CustomerAudit::whereKey($eski->id)->update(['created_at' => now()->subDays(CustomerAudit::SAKLAMA_GUNU + 1)]);
    $sahipsiz = CustomerAudit::record('deleted', null);
    $ozet = CustomerAudit::record('imported', null, ['eklenen' => 1]);

    $this->artisan('model:prune', ['--model' => [CustomerAudit::class]])->assertSuccessful();

    expect(CustomerAudit::find($eski->id))->toBeNull()
        ->and(CustomerAudit::find($sahipsiz->id))->toBeNull()
        ->and(CustomerAudit::find($ozet->id))->not->toBeNull()
        ->and(CustomerAudit::where('customer_id', $musteri->id)->where('action', 'created')->exists())->toBeTrue();
});
