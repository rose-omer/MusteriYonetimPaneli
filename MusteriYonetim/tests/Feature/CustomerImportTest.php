<?php

use App\Enums\OdemeDurumu;
use App\Enums\TehlikeSinifi;
use App\Exports\CustomerTemplateExport;
use App\Filament\Pages\ImportCustomers;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\CustomerAudit;
use App\Models\Faaliyet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Livewire\livewire;

/**
 * Gerçek .xlsx dosyası üretir (şablonla aynı düzen: başlıklar 2. satırda).
 *
 * @param  list<array<string, mixed>>  $rows
 */
function makeCustomerWorkbook(array $rows, string $name = 'liste.xlsx'): string
{
    Excel::store(new CustomerTemplateExport($rows), $name, 'local');

    return $name;
}

function row(array $overrides = []): array
{
    return [
        'firma_no' => '10001',
        'hizmet_alan_isyeri_unvani' => 'Örnek Metal Ltd. Şti.',
        'faaliyet' => 'Metal işleme',
        'tehlike_sinifi' => 'Çok Tehlikeli',
        'telefon_numarasi' => '0532 000 00 01',
        'il' => 'Bursa',
        'tutar' => '1.250,50',
        'odeme_durumu' => 'ÖDENMEMİŞ',
        'sozlesme_onay_durumu' => 'Evet',
        'firma_ziyareti' => '15.03.2025',
        ...$overrides,
    ];
}

beforeEach(function () {
    Storage::fake('local');
    actingAsRole('admin');
});

it('Türkçe biçimli tutarı, tarihi ve seçenek metnini doğru kaydeder', function () {
    $file = makeCustomerWorkbook([row()]);

    Excel::import($import = new CustomersImport, $file, 'local');

    $customer = Customer::sole();

    expect($import->inserted)->toBe(1)
        ->and($customer->tutar)->toBe('1250.50')
        ->and($customer->tehlike_sinifi)->toBe(TehlikeSinifi::CokTehlikeli)
        ->and($customer->odeme_durumu)->toBe(OdemeDurumu::Odenmemis)
        ->and($customer->sozlesme_onay_durumu)->toBeTrue()
        ->and($customer->firma_ziyareti->format('Y-m-d'))->toBe('2025-03-15')
        ->and($customer->faaliyet->isim)->toBe('Metal işleme');
});

it('aynı dosya iki kez içe aktarılınca kayıt sayısı değişmez', function () {
    $file = makeCustomerWorkbook([row(), row(['firma_no' => '10002', 'hizmet_alan_isyeri_unvani' => 'İkinci Firma A.Ş.'])]);

    Excel::import(new CustomersImport, $file, 'local');
    Excel::import($second = new CustomersImport, $file, 'local');

    expect(Customer::count())->toBe(2)
        ->and($second->inserted)->toBe(0)
        ->and($second->updated)->toBe(2)
        ->and(Faaliyet::count())->toBe(1);
});

it('upsert ilk oluşturma tarihini korur ve silinmiş kaydı geri getirir', function () {
    $existing = Customer::factory()->create(['firma_no' => '10001', 'created_at' => now()->subYear()]);
    $existing->delete();

    Excel::import(new CustomersImport, makeCustomerWorkbook([row(['tutar' => '99'])]), 'local');

    $fresh = Customer::withTrashed()->sole();
    expect($fresh->trashed())->toBeFalse()
        ->and($fresh->tutar)->toBe('99.00')
        ->and($fresh->created_at->year)->toBe(now()->subYear()->year);
});

it('hatalı satırları satır numarasıyla raporlar ve atlar', function () {
    $file = makeCustomerWorkbook([
        row(),
        row(['firma_no' => '10002', 'tutar' => 'bin lira']),
        row(['firma_no' => '10003', 'hizmet_alan_isyeri_unvani' => null]),
        row(['firma_no' => '10004', 'odeme_durumu' => 'Belki']),
    ]);

    Excel::import($import = new CustomersImport, $file, 'local');

    $failures = $import->failures()->mapWithKeys(fn ($f) => [$f->row() => implode(' ', $f->errors())]);

    expect(Customer::count())->toBe(1)
        ->and($import->skipped())->toBe(3)
        ->and($failures->keys()->all())->toEqualCanonicalizing([4, 5, 6])
        ->and($failures[4])->toContain('Tutar sayı değil');
});

it('içe aktarım sayfası önizler, aktarır ve sonuç özeti gösterir', function () {
    $path = makeCustomerWorkbook([row(), row(['firma_no' => '10002', 'tutar' => 'abc'])], 'kaynak.xlsx');
    $upload = UploadedFile::fake()->createWithContent('musteriler.xlsx', Storage::disk('local')->get($path));

    $page = livewire(ImportCustomers::class)
        ->fillForm(['file' => $upload])
        ->call('buildPreview')
        ->assertSet('step', 2)
        ->assertSet('preview.total', 2)
        ->assertSet('preview.valid', 1)
        ->assertSet('preview.invalid', 1)
        ->assertSee('Tutar sayı değil');

    expect(Customer::count())->toBe(0);

    $page->call('runImport')
        ->assertSet('step', 3)
        ->assertSet('result.inserted', 1)
        ->assertSet('result.skipped', 1)
        ->assertSee('Hatalı satırları indir');

    $audit = CustomerAudit::where('action', 'imported')->sole();

    expect(Customer::count())->toBe(1)
        ->and($audit->changed_fields['eklenen'])->toBe(1)
        ->and($audit->changed_fields['firma_no'])->toBe(['10001']);
});

it('istemci içe aktarım dosya yolunu değiştiremez', function () {
    livewire(ImportCustomers::class)->set('storedPath', 'imports/baskasi.xlsx');
})->throws(CannotUpdateLockedPropertyException::class);

it('dosya ortasında hata olursa hiçbir satırı yazmaz', function () {
    $rows = array_map(fn (int $i) => row(['firma_no' => (string) (20000 + $i)]), range(1, CustomersImport::BATCH_SIZE + 5));
    $path = makeCustomerWorkbook($rows, 'kaynak.xlsx');
    $upload = UploadedFile::fake()->createWithContent('musteriler.xlsx', Storage::disk('local')->get($path));

    $page = livewire(ImportCustomers::class)
        ->fillForm(['file' => $upload])
        ->call('buildPreview')
        ->assertSet('step', 2);

    // İkinci parçada beklenmeyen hata: ilk parçanın yazdığı satırlar da geri alınmalı.
    $calls = 0;
    DB::listen(function ($query) use (&$calls) {
        if (str_starts_with(strtolower($query->sql), 'insert into "customers"') && ++$calls === 2) {
            throw new RuntimeException('Disk dolu');
        }
    });

    $page->call('runImport')
        ->assertSet('step', 2)
        ->assertNotified('İçe aktarım tamamlanmadı');

    expect(Customer::count())->toBe(0);
});

it('başlıkları tanınmayan dosyayı önizlemeye almaz', function () {
    Excel::store(new class implements FromArray
    {
        public function array(): array
        {
            return [['Not'], ['Ad', 'Soyad'], ['Ali', 'Veli']];
        }
    }, 'yanlis.xlsx', 'local');

    livewire(ImportCustomers::class)
        ->fillForm(['file' => UploadedFile::fake()->createWithContent('yanlis.xlsx', Storage::disk('local')->get('yanlis.xlsx'))])
        ->call('buildPreview')
        ->assertSet('step', 1)
        ->assertNotified('Başlıklar tanınmadı');
});

it('örnek şablonu admin indirir', function () {
    Excel::fake();

    $this->get('/sablon/musteri-listesi')->assertOk();

    Excel::assertDownloaded('ornek-musteri-listesi.xlsx', fn (CustomerTemplateExport $export) => count($export->array()) === 4);
});
