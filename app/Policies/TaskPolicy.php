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
        // Admin, Kierownik i Liderzy mogą tworzyć zadania
        return $user->isAdmin() || $user->isKierownik() || $user->isLider();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin i Kierownik mogą edytować wszystko
        if ($user->isAdmin() || $user->isKierownik()) {
            return true;
        }
        
        // Lider może edytować tylko swoje zadania, ale nie te ze statusem "accepted"
        if ($user->isLider() && $task->leader_id === $user->id) {
            return !$task->isLockedForUser($user);
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Tylko Admin i Kierownik mogą usuwać zadania
        return $user->isAdmin() || $user->isKierownik();
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