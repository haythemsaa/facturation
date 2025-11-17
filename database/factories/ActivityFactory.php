<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'task', 'note']),
            'subject' => fake()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'scheduled_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'completed_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'duration_minutes' => fake()->optional()->numberBetween(15, 240),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
        ];
    }
}
