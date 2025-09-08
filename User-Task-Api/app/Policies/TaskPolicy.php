<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     * Admin sees all, user sees their own.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'user']);
    }

    /**
     * Determine whether the user can view a specific task.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->role === 'admin' || $user->id === $task->user_id;
    }

    /**
     * Determine whether the user can create tasks.
     * Only admin can create/assign tasks.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the task.
     * Admin → any task; User → only their own.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->role === 'admin' || $user->id === $task->user_id;
    }

    /**
     * Determine whether the user can delete the task.
     * Only admin can delete tasks.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->role === 'admin';
    }
}
