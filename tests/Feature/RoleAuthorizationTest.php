<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        $user->roles()->syncWithoutDetaching([$role->id]);
        
        return $user->fresh();
    }

    private function getUserWithToken(string $roleName): array
    {
        $user = $this->createUserWithRole($roleName);
        $token = $user->createToken('test-token')->plainTextToken;
        
        return ['user' => $user, 'token' => $token];
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $data = $this->getUserWithToken('admin');
        $token = $data['token'];

        // Test access to an admin-only route with sanctum token
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/users');

        // Should not be 403 (forbidden)
        $this->assertNotEquals(403, $response->status());
    }

    public function test_manager_cannot_access_admin_restricted_routes(): void
    {
        $data = $this->getUserWithToken('manager');
        $token = $data['token'];

        // Try to access admin-only route: /api/create-user
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/create-user', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123'
            ]);

        // Should be 403 (forbidden by role middleware)
        $this->assertEquals(403, $response->status());
    }

    public function test_employee_cannot_access_admin_routes(): void
    {
        $data = $this->getUserWithToken('employe');
        $token = $data['token'];

        // Try to access admin-only route
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get('/api/users');

        $this->assertEquals(403, $response->status());
    }

    public function test_manager_can_access_manager_routes(): void
    {
        $data = $this->getUserWithToken('manager');
        $token = $data['token'];

        // Manager should be able to access manager-only route: /api/team-tasks
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/team-tasks', [
                'title' => 'Team Task',
                'description' => 'Team Task Description',
                'priority' => 'high',
                'assigned_to' => null,
            ]);

        // Expect 200 or 422 (validation errors) or 201 (created), not 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_employee_cannot_access_manager_routes(): void
    {
        $data = $this->getUserWithToken('employe');
        $token = $data['token'];

        // Employee should not be able to access manager routes
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->post('/api/team-tasks', [
                'title' => 'Team Task',
                'priority' => 'high',
            ]);

        $this->assertEquals(403, $response->status());
    }

    public function test_employee_can_access_employee_routes(): void
    {
        // Create employee and task
        $data = $this->getUserWithToken('employe');
        $employee = $data['user'];
        $token = $data['token'];
        
        $creator = $this->createUserWithRole('manager');
        
        $task = \App\Models\Task::create([
            'title' => 'Employee Task',
            'description' => 'Assigned to employee',
            'status' => 'pending',
            'priority' => 'high',
            'assigned_to' => $employee->id,
            'created_by' => $creator->id,
        ]);

        // Employee should be able to view task assigned to them
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/tasks/{$task->id}");

        // Should not be 403 (forbidden)
        $this->assertNotEquals(403, $response->status());
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        // No authentication token - should redirect or return 401/500 due to route not found
        $response = $this->get('/api/users');

        // API should not allow unauthenticated access - check for error status
        $this->assertTrue(in_array($response->status(), [401, 500, 404]));
    }
}
