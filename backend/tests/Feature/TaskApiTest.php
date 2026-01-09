<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_tasks()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_can_create_task()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $data = [
            'level' => 2,
            'status' => 'working',
            'user_id' => $user->id,
            'project' => 'B2C',
            'department' => 'R&D',
            'work' => 'Implement Auth',
            'points' => 5.5,
            'release_date' => '2026-01-01',
            'memo' => 'Test task memo',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['work' => 'Implement Auth']);

        $this->assertDatabaseHas('tasks', ['work' => 'Implement Auth']);
    }

    public function test_user_can_create_task_with_defaults()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $data = [
            'user_id' => $user->id,
            'project' => 'B2C',
            'department' => 'R&D',
            'work' => 'Task with defaults',
            'points' => 1.0,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', $data);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'work' => 'Task with defaults',
                'level' => 1,
                'status' => 'in progress'
            ]);

        $this->assertDatabaseHas('tasks', [
            'work' => 'Task with defaults',
            'level' => 1,
            'status' => 'in progress'
        ]);
    }

    public function test_user_can_update_task()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $newUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'user_id' => $newUser->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('user_id', $newUser->id);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $newUser->id,
        ]);
    }
}
