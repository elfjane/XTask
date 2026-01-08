<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_user_can_update_profile_info()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_cannot_update_email_to_existing_one()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_update_password()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'current_password' => 'password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertTrue(Hash::check('new_password', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'current_password' => 'wrong_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }
}
