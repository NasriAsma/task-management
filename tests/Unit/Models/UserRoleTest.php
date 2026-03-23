<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_role_returns_true_when_user_has_role(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => 'admin']);
        
        $user->roles()->syncWithoutDetaching([$role->id]);
        $user = $user->fresh();

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_has_role_returns_false_when_user_does_not_have_role(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => 'manager']);
        
        $user->roles()->syncWithoutDetaching([$role->id]);
        $user = $user->fresh();

        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('manager'));
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
        $managerRole = Role::query()->firstOrCreate(['name' => 'manager']);
        
        $user->roles()->syncWithoutDetaching([$adminRole->id, $managerRole->id]);
        $user = $user->fresh();

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('manager'));
    }
}
