<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function viewTeam(User $user): bool
    {
        return $user->hasRole('manager');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('admin') && $user->id !== $model->id;
    }
}
