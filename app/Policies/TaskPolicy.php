<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Autoriser tout utilisateur connecté à créer une tâche
    public function create(User $user)
    {
        return true; 
    }

    // Voir, modifier ou supprimer : Seulement si on est le créateur
    public function view(User $user, Task $task)
    {
        return $user->id === $task->created_by || $user->id === $task->assigned_to;
    }

    public function update(User $user, Task $task)
    {
        return $user->id === $task->created_by;
    }

    public function delete(User $user, Task $task)
    {
        return $user->id === $task->created_by;
    }

    // Règle spéciale : L'assigné peut changer le statut, mais pas le titre
    public function updateStatus(User $user, Task $task)
    {
        return $user->id === $task->assigned_to || $user->id === $task->created_by;
    }

    // Statistiques globales : Seulement pour les admins
    public function viewGlobalStats(User $user)
    {
        return $user->hasRole('admin');
    }
}