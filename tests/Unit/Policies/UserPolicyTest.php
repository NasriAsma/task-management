<?php

namespace Tests\Unit\Policies;

use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }

    public function test_admin_can_create_user(): void
    {
        $policy = new UserPolicy();
        $admin = $this->createUserWithRole('admin');

        $this->assertTrue($policy->create($admin));
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $policy = new UserPolicy();
        $manager = $this->createUserWithRole('manager');

        $this->assertFalse($policy->create($manager));
    }

    public function test_only_admin_or_manager_can_view_any_users(): void
    {
        $policy = new UserPolicy();
        $admin = $this->createUserWithRole('admin');
        $manager = $this->createUserWithRole('manager');
        $employe = $this->createUserWithRole('employe');

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($manager));
        $this->assertFalse($policy->viewAny($employe));
    }

    public function test_user_can_update_self_and_admin_can_update_any_user(): void
    {
        $policy = new UserPolicy();
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('employe');
        $other = $this->createUserWithRole('manager');

        $this->assertTrue($policy->update($user, $user));
        $this->assertTrue($policy->update($admin, $other));
        $this->assertFalse($policy->update($other, $user));
    }

    public function test_only_admin_can_delete_other_users_not_self(): void
    {
        $policy = new UserPolicy();
        $admin = $this->createUserWithRole('admin');
        $target = $this->createUserWithRole('employe');
        $manager = $this->createUserWithRole('manager');

        $this->assertTrue($policy->delete($admin, $target));
        $this->assertFalse($policy->delete($admin, $admin));
        $this->assertFalse($policy->delete($manager, $target));
    }
}
