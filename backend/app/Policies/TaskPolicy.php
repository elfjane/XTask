<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can view tasks, but index method should filter results
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $user->isManager() || $user->isAuditor() || $user->isTaskUser() || $user->isExecutor()) {
            return true;
        }

        // Executor can only view tasks from their department, or where they are assigned/related
        if ($user->isExecutor()) {
            return $task->department_id === $user->department_id ||
                $task->assignee_id === $user->id ||
                str_contains($task->related_personnel ?? '', $user->name);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager() || $user->isTaskUser() || $user->isExecutor();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Manager and Task User can edit any task
        if ($user->isAdmin() || $user->isManager() || $user->isTaskUser()) {
            return true;
        }

        if ($user->isAuditor()) {
            return true;
        }

        if ($user->id === $task->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
}
