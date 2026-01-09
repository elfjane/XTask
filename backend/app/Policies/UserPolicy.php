<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAuditor();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAuditor();
    }

    public function create(User $user): bool
    {
        return false; // Admin only via before()
    }

    public function update(User $user, User $model): bool
    {
        return false; // Admin only via before()
    }

    public function delete(User $user, User $model): bool
    {
        return false; // Admin only via before()
    }
}
