<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Voir la liste (Général)
    public function viewAny(User $user) { return true; }

    // Voir une tâche précise
    public function view(User $user, Task $task)
    {
        return $user->id === $task->created_by || $user->id === $task->assigned_to;
    }

    // Modifier ou supprimer (Seul le créateur peut tout changer)
    public function update(User $user, Task $task)
    {
        return $user->id === $task->created_by;
    }

    public function delete(User $user, Task $task)
    {
        return $user->id === $task->created_by;
    }

    // Changer seulement le statut (L'assigné a le droit)
    public function updateStatus(User $user, Task $task)
    {
        return $user->id === $task->assigned_to || $user->id === $task->created_by;
    }

    // Stats globales (Admin seulement)
    public function viewGlobalStats(User $user)
    {
        return $user->role === 'admin';
    }
}