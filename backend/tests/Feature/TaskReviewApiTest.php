<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_status_becomes_submitted_when_task_is_finished()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'working']);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task->id}", [
            'status' => 'finished',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'finished',
            'review_status' => 'submitted',
        ]);
    }

    public function test_review_status_resets_when_task_is_no_longer_finished()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id, 'status' => 'finished', 'review_status' => 'submitted']);

        $response = $this->actingAs($user)->putJson("/api/tasks/{$task->id}", [
            'status' => 'working',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'working',
            'review_status' => 'unsubmitted',
        ]);
    }

    public function test_auditor_can_approve_task()
    {
        $auditor = User::factory()->create(['role' => 'auditor']);
        $task = Task::factory()->create(['status' => 'finished', 'review_status' => 'submitted']);

        $response = $this->actingAs($auditor)->putJson("/api/tasks/{$task->id}", [
            'review_status' => 'approved',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'review_status' => 'approved',
        ]);
    }

    public function test_auditor_can_fail_task()
    {
        $auditor = User::factory()->create(['role' => 'auditor']);
        $task = Task::factory()->create(['status' => 'finished', 'review_status' => 'submitted']);

        $response = $this->actingAs($auditor)->putJson("/api/tasks/{$task->id}", [
            'review_status' => 'failed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'failed',
            'review_status' => 'failed',
        ]);
    }

    public function test_index_can_filter_by_review_status()
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'review_status' => 'submitted']);
        Task::factory()->create(['user_id' => $user->id, 'review_status' => 'unsubmitted']);

        $response = $this->actingAs($user)->getJson("/api/tasks?review_status=submitted");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_index_can_exclude_review_status()
    {
        $user = User::factory()->create();
        Task::factory()->create(['user_id' => $user->id, 'review_status' => 'approved']);
        Task::factory()->create(['user_id' => $user->id, 'review_status' => 'unsubmitted']);

        $response = $this->actingAs($user)->getJson("/api/tasks?exclude_review_status=approved");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }
}
