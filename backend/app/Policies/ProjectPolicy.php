<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
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
        return $user->isAuditor() || $user->isManager();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isAuditor() || $user->isManager();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isManager();
    }
}
