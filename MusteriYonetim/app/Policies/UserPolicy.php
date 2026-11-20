<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** Kullanıcı yönetimi yalnız admin içindir. Admin kendi hesabını silemez. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin && ! $user->is($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
