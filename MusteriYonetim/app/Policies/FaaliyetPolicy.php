<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Faaliyet;
use App\Models\User;

/** Faaliyet listesini herkes görür, yalnız admin değiştirir. */
class FaaliyetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Faaliyet $faaliyet): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Faaliyet $faaliyet): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Faaliyet $faaliyet): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }
}
