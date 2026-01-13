<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'task_user']);
        $this->auditor = User::factory()->create(['role' => 'auditor']);
    }

    public function test_admin_can_view_statistics()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/tasks/statistics');
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_view_statistics()
    {
        $response = $this->actingAs($this->user)->getJson('/api/tasks/statistics');
        $response->assertStatus(403);

        $response = $this->actingAs($this->auditor)->getJson('/api/tasks/statistics');
        $response->assertStatus(403);
    }

    public function test_statistics_calculation_logic()
    {
        // Create tasks in different months
        $month1 = now()->subMonths(1);
        $month2 = now();

        // Task 1: Finished in Month 1
        Task::factory()->create([
            'status' => 'finished',
            'points' => 5,
            'actual_finish_date' => $month1,
            'user_id' => $this->user->id
        ]);

        // Task 2: Approved in Month 1 (should use approved_at)
        Task::factory()->create([
            'status' => 'finished',
            'review_status' => 'approved',
            'points' => 3,
            'actual_finish_date' => $month1->copy()->subDays(5),
            'approved_at' => $month1,
            'user_id' => $this->user->id
        ]);

        // Task 3: Finished in Month 2
        Task::factory()->create([
            'status' => 'finished',
            'points' => 2,
            'actual_finish_date' => $month2,
            'user_id' => $this->user->id
        ]);

        // Task 4: Unfinished (should not be counted)
        Task::factory()->create([
            'status' => 'in progress',
            'points' => 10,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/tasks/statistics');
        $response->assertStatus(200);

        $data = $response->json();

        // Find Month 1 stats
        $statsMonth1 = collect($data)->firstWhere('month', $month1->format('Y-m'));
        $this->assertNotNull($statsMonth1);
        $this->assertEquals(8, $statsMonth1['total_points']); // 5 + 3

        // Find Month 2 stats
        $statsMonth2 = collect($data)->firstWhere('month', $month2->format('Y-m'));
        $this->assertNotNull($statsMonth2);
        $this->assertEquals(2, $statsMonth2['total_points']);
    }
}
