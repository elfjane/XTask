<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_schedules()
    {
        $user = User::factory()->create();
        Schedule::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/schedules');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_can_create_schedule()
    {
        $user = User::factory()->create();

        $data = [
            'project' => 'Test Project',
            'title' => 'Test Schedule',
            'status' => 'working',
            'confirm' => 'Confirmed',
            'deadline' => '2026-01-31',
            'scheduled_start' => '2026-01-01',
            'scheduled_end' => '2026-01-15',
            'memo' => 'Test memo',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/schedules', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Schedule']);

        $this->assertDatabaseHas('schedules', ['title' => 'Test Schedule']);
    }
}
