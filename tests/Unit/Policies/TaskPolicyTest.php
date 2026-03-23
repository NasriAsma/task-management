<?php

namespace Tests\Unit\Policies;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->fresh();
    }

    public function test_any_authenticated_user_can_create_task(): void
    {
        $policy = new TaskPolicy();
        $user = $this->createUserWithRole('employe');

        $this->assertTrue($policy->create($user));
    }

    public function test_view_is_allowed_for_creator_or_assignee(): void
    {
        $policy = new TaskPolicy();
        $creator = $this->createUserWithRole('manager');
        $assignee = $this->createUserWithRole('employe');
        $other = $this->createUserWithRole('employe');

        $task = new Task([
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        $this->assertTrue($policy->view($creator, $task));
        $this->assertTrue($policy->view($assignee, $task));
        $this->assertFalse($policy->view($other, $task));
    }

    public function test_only_creator_can_update_and_delete_task(): void
    {
        $policy = new TaskPolicy();
        $creator = $this->createUserWithRole('manager');
        $assignee = $this->createUserWithRole('employe');

        $task = new Task([
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        $this->assertTrue($policy->update($creator, $task));
        $this->assertFalse($policy->update($assignee, $task));
        $this->assertTrue($policy->delete($creator, $task));
        $this->assertFalse($policy->delete($assignee, $task));
    }

    public function test_update_status_is_allowed_for_creator_or_assignee(): void
    {
        $policy = new TaskPolicy();
        $creator = $this->createUserWithRole('manager');
        $assignee = $this->createUserWithRole('employe');
        $other = $this->createUserWithRole('employe');

        $task = new Task([
            'created_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);

        $this->assertTrue($policy->updateStatus($creator, $task));
        $this->assertTrue($policy->updateStatus($assignee, $task));
        $this->assertFalse($policy->updateStatus($other, $task));
    }

    public function test_only_admin_can_view_global_stats(): void
    {
        $policy = new TaskPolicy();
        $admin = $this->createUserWithRole('admin');
        $manager = $this->createUserWithRole('manager');

        $this->assertTrue($policy->viewGlobalStats($admin));
        $this->assertFalse($policy->viewGlobalStats($manager));
    }
}
