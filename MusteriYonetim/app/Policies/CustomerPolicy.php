<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;

/**
 * Yetki matrisi:
 * - viewAny / view: bütün roller
 * - create / update: admin, editor
 * - delete / restore / import: admin
 * - forceDelete: kimse (kalıcı silme zamanlanmış "model:prune" komutuyla yapılır)
 */
class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Customer $customer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->canWrite();
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->role->canWrite();
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function restoreAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function import(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /** Toplu durum değişikliği ve dışa aktarım. */
    public function updateAny(User $user): bool
    {
        return $user->role->canWrite();
    }

    public function export(User $user): bool
    {
        return $user->role->canWrite();
    }
}
