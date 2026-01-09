<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleMemoApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_can_add_memo_to_schedule()
    {
        $schedule = Schedule::create([
            'project' => 'Test Project',
            'title' => 'Test Schedule',
            'status' => 'working',
            'confirm' => 'Tentatively',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/schedules/{$schedule->id}/memos", [
                'content' => 'This is a test memo',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'schedule_id' => $schedule->id,
                'content' => 'This is a test memo',
                'user_name' => $this->user->name,
            ]);

        $this->assertDatabaseHas('memos', [
            'schedule_id' => $schedule->id,
            'content' => 'This is a test memo',
            'user_name' => $this->user->name,
        ]);
    }

    public function test_cannot_add_empty_memo()
    {
        $schedule = Schedule::create([
            'project' => 'Test Project',
            'title' => 'Test Schedule',
            'status' => 'working',
            'confirm' => 'Tentatively',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/schedules/{$schedule->id}/memos", [
                'content' => '',
            ]);

        $response->assertStatus(422);
    }
}
