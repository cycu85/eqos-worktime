<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Wszyscy zalogowani mogą widzieć listę zadań
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin i kierownik widzą wszystko
        if ($user->isAdmin() || $user->isKierownik()) {
            return true;
        }
        
        // Lider widzi tylko swoje zadania
        if ($user->isLider() && $task->leader_id === $user->id) {
            return true;
        }
        
        // Pracownik widzi zadania gdzie jest w zespole
        if ($user->isPracownik() && $task->team) {
            $teamMembers = array_map('trim', explode(',', $task->team));
            return in_array($user->name, $teamMembers);
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Tylko liderzy i admin mogą tworzyć zadania
        return $user->isAdmin() || $user->isLider();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin może edytować wszystko, lider tylko swoje zadania
        return $user->isAdmin() || $task->leader_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Admin może usuwać wszystko, lider tylko swoje zadania
        return $user->isAdmin() || $task->leader_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin();
    }
}