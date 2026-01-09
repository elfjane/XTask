<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_projects()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Project::create(['name' => 'Alpha']);

        $response = $this->actingAs($admin)->getJson('/api/admin/projects');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Alpha']);
    }

    public function test_admin_can_create_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/admin/projects', [
            'name' => 'Beta',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'Beta']);
    }

    public function test_admin_can_update_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->putJson("/api/admin/projects/{$project->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['name' => 'New Name']);
    }

    public function test_user_cannot_access_projects()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->getJson('/api/admin/projects');

        $response->assertStatus(403);
    }
}
