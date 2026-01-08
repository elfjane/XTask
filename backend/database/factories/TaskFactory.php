<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'level' => $this->faker->numberBetween(1, 3),
            'status' => $this->faker->randomElement(['working', 'in progress', 'cancelled', 'idle', 'waiting qa', 'finished', 'miss']),
            'user_id' => User::factory(),
            'project' => $this->faker->word,
            'department' => $this->faker->word,
            'work' => $this->faker->sentence,
            'points' => $this->faker->randomFloat(1, 0.5, 21),
            'release_date' => $this->faker->date(),
            'memo' => $this->faker->paragraph,
        ];
    }
}
