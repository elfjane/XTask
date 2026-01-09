<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_departments()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Department::create(['name' => 'IT']);

        $response = $this->actingAs($admin)->getJson('/api/admin/departments');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'IT']);
    }

    public function test_admin_can_create_department()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/admin/departments', [
            'name' => 'New Dept',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('departments', ['name' => 'New Dept']);
    }

    public function test_admin_can_update_department()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->putJson("/api/admin/departments/{$dept->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('departments', ['name' => 'New Name']);
    }

    public function test_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->getJson('/api/admin/departments');

        $response->assertStatus(403);
    }
}
