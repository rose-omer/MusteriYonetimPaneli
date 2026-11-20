<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/**
 * Verilen rolde kullanıcıyla oturum açar ve paneli etkin yapar.
 */
function actingAsRole(string $role): User
{
    $user = User::factory()->{$role}()->create();
    test()->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    return $user;
}
