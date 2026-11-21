<?php

use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Models\Customer;
use App\Models\Faaliyet;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

use function Pest\Livewire\livewire;

describe('oturumsuz erişim', function () {
    it('panel sayfalarını giriş sayfasına yönlendirir', function (string $url) {
        $this->get($url)->assertRedirect('/admin/login');
    })->with([
        '/admin',
        '/admin/customers',
        '/admin/customers/create',
        '/admin/ice-aktar',
        '/admin/faaliyetler',
        '/admin/kullanicilar',
    ]);

    it('eski rotalarla müşteri ekleyemez', function () {
        $this->post('/customers', ['hizmet_alan_isyeri_unvani' => 'Saldırgan A.Ş.'])
            ->assertRedirect('/admin/customers');

        $this->get('/admin/customers')->assertRedirect('/admin/login');
        expect(Customer::count())->toBe(0);
    });

    it('şablon indirme bağlantısını giriş sayfasına yönlendirir', function () {
        $this->get('/sablon/musteri-listesi')->assertRedirect('/admin/login');
    });

    it('kayıt sayfası yoktur', function () {
        $this->get('/admin/register')->assertNotFound();
    });
});

describe('yetki matrisi (CustomerPolicy)', function () {
    it('rol ve işlem için doğru kararı verir', function (string $role, string $ability, bool $expected) {
        $user = User::factory()->{$role}()->create();
        $customer = Customer::factory()->create();
        $argument = in_array($ability, ['view', 'update', 'delete', 'restore', 'forceDelete'], true) ? $customer : Customer::class;

        expect(Gate::forUser($user)->allows($ability, $argument))->toBe($expected);
    })->with([
        ['admin', 'viewAny', true], ['admin', 'view', true], ['admin', 'create', true],
        ['admin', 'update', true], ['admin', 'delete', true], ['admin', 'import', true],
        ['editor', 'viewAny', true], ['editor', 'view', true], ['editor', 'create', true],
        ['editor', 'update', true], ['editor', 'delete', false], ['editor', 'import', false],
        ['viewer', 'viewAny', true], ['viewer', 'view', true], ['viewer', 'create', false],
        ['viewer', 'update', false], ['viewer', 'delete', false], ['viewer', 'import', false],
        ['admin', 'forceDelete', false],
    ]);

    it('faaliyet listesini yalnız admin değiştirir', function (string $role, bool $expected) {
        $user = User::factory()->{$role}()->create();

        expect(Gate::forUser($user)->allows('create', Faaliyet::class))->toBe($expected);
    })->with([['admin', true], ['editor', false], ['viewer', false]]);
});

describe('panel sayfaları', function () {
    it('izleyici ekleme ve düzenleme sayfasında 403 alır', function () {
        actingAsRole('viewer');
        $customer = Customer::factory()->create();

        $this->get('/admin/customers/create')->assertForbidden()->assertSee('Bu işlem için yetkiniz yok');
        $this->get("/admin/customers/{$customer->id}/edit")->assertForbidden();
        $this->get("/admin/customers/{$customer->id}")->assertOk();
    });

    it('düzenleyici ekler ama içe aktaramaz', function () {
        actingAsRole('editor');

        $this->get('/admin/customers/create')->assertOk();
        $this->get('/admin/ice-aktar')->assertForbidden();
        $this->get('/sablon/musteri-listesi')->assertForbidden();
    });

    it('kullanıcı yönetimini yalnız admin açar', function (string $role, int $status) {
        actingAsRole($role);

        $this->get('/admin/kullanicilar')->assertStatus($status);
    })->with([['admin', 200], ['editor', 403], ['viewer', 403]]);

    it('düzenleyici silme düğmesini görmez, admin görür', function () {
        $customer = Customer::factory()->create();

        actingAsRole('editor');
        livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])->assertActionHidden('delete');

        actingAsRole('admin');
        livewire(ViewCustomer::class, ['record' => $customer->getRouteKey()])->assertActionVisible('delete');
    });

    it('izleyici kayıtlı veriyi değiştiremez', function () {
        actingAsRole('viewer');
        $customer = Customer::factory()->create();

        livewire(EditCustomer::class, ['record' => $customer->getRouteKey()])->assertForbidden();
    });
});
