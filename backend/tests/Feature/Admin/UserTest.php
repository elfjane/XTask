<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'John Doe']);

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'John Doe']);
    }

    public function test_admin_can_create_user_with_relations()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $dept = Department::create(['name' => 'IT']);
        $project = Project::create(['name' => 'Project A']);

        $response = $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'employee_id' => 'EMP001',
            'department_id' => $dept->id,
            'role' => 'user',
            'projects' => [$project->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'department_id' => $dept->id]);
        $user = User::where('email', 'new@example.com')->first();
        $this->assertTrue($user->projects->contains($project));
    }

    public function test_admin_can_update_user_relations()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $project1 = Project::create(['name' => 'P1']);
        $project2 = Project::create(['name' => 'P2']);

        $response = $this->actingAs($admin)->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated User',
            'email' => $user->email,
            'projects' => [$project1->id, $project2->id],
        ]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertCount(2, $user->projects);
    }

    public function test_admin_can_freeze_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }

    public function test_frozen_user_cannot_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'is_active' => false
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
