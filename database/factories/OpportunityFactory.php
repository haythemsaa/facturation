<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->catchPhrase(),
            'description' => fake()->optional()->paragraph(),
            'value' => fake()->randomFloat(3, 1000, 100000),
            'probability' => fake()->randomElement([10, 25, 50, 75, 90]),
            'expected_close_date' => fake()->dateTimeBetween('now', '+6 months'),
            'actual_close_date' => null,
            'status' => fake()->randomElement(['open', 'won', 'lost']),
            'lost_reason' => null,
        ];
    }
}
