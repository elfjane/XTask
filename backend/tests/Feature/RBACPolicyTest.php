<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RBACPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_user_management()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->getJson('/api/admin/users');
        $response->assertStatus(200);
    }

    public function test_manager_cannot_access_user_management()
    {
        $manager = User::where('role', 'manager')->first();
        $response = $this->actingAs($manager)->getJson('/api/admin/users');
        $response->assertStatus(403);
    }

    public function test_auditor_can_view_users_but_not_edit()
    {
        $auditor = User::where('role', 'auditor')->first();
        $response = $this->actingAs($auditor)->getJson('/api/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($auditor)->postJson('/api/admin/users', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'password',
            'role' => 'executor',
            'department_id' => 1,
            'projects' => []
        ]);
        $response->assertStatus(403);
    }

    public function test_executor_cannot_access_admin_api()
    {
        $executor = User::where('role', 'executor')->first();
        $response = $this->actingAs($executor)->getJson('/api/admin/projects');
        $response->assertStatus(403);
    }

    public function test_manager_can_create_project()
    {
        $manager = User::where('role', 'manager')->first();
        $response = $this->actingAs($manager)->postJson('/api/admin/projects', [
            'name' => 'New Project'
        ]);
        $response->assertStatus(201);
    }
}
