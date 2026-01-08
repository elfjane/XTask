<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project' => $this->faker->word,
            'title' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['working', 'finish', 'in_progress', 'fail']),
            'confirm' => $this->faker->randomElement(['Tentatively', 'Confirmed']),
            'deadline' => $this->faker->date(),
            'scheduled_start' => $this->faker->date(),
            'scheduled_end' => $this->faker->date(),
            'memo' => $this->faker->paragraph,
        ];
    }
}
