<?php

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Livewire\livewire;

it('admin kullanıcı ekler ve davet e-postası gönderir', function () {
    Notification::fake();
    actingAsRole('admin');

    livewire(CreateUser::class)
        ->fillForm(['name' => 'Yeni Kişi', 'email' => 'yeni@demo.test', 'role' => UserRole::Editor->value, 'davet_gonder' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'yeni@demo.test')->sole();

    expect($user->role)->toBe(UserRole::Editor);
    Notification::assertSentTo($user, ResetPassword::class);
});

it('davetsiz eklemede geçici parola zorunludur', function () {
    actingAsRole('admin');

    livewire(CreateUser::class)
        ->fillForm(['name' => 'Kişi', 'email' => 'kisi@demo.test', 'role' => UserRole::Viewer->value, 'davet_gonder' => false])
        ->call('create')
        ->assertHasFormErrors(['password' => 'required']);
});

it('admin kendi hesabını silemez', function () {
    $admin = actingAsRole('admin');

    livewire(ListUsers::class)
        ->assertActionHidden(TestAction::make(DeleteAction::class)->table($admin));
});

it('yeni kullanıcı varsayılan olarak izleyicidir', function () {
    expect((new User)->role)->toBe(UserRole::Viewer);
});

it('tek yönetici kendi rolünü düşüremez', function () {
    $admin = actingAsRole('admin');

    livewire(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm(['role' => UserRole::Viewer->value])
        ->call('save')
        ->assertHasFormErrors(['role']);

    expect($admin->fresh()->role)->toBe(UserRole::Admin);
});

it('başka yönetici varsa rol düşürülür', function () {
    $admin = actingAsRole('admin');
    User::factory()->admin()->create();

    livewire(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm(['role' => UserRole::Editor->value])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($admin->fresh()->role)->toBe(UserRole::Editor);
});
