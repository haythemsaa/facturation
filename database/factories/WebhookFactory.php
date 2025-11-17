<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allEvents = [
            'document.created',
            'document.validated',
            'document.paid',
            'opportunity.won',
            'opportunity.lost',
            'stock.low',
            'employee.hired',
            'payslip.generated',
        ];

        // Select 2-5 random events
        $eventCount = fake()->numberBetween(2, 5);
        $events = fake()->randomElements($allEvents, $eventCount);

        return [
            'name' => fake()->randomElement([
                'Webhook Zapier',
                'Webhook Make.com',
                'Webhook N8N',
                'Webhook Slack',
                'Webhook Discord',
                'Webhook Custom',
            ]),
            'url' => fake()->url(),
            'events' => json_encode($events),
            'secret' => bin2hex(random_bytes(32)),
            'is_active' => fake()->boolean(85), // 85% active
            'max_retries' => fake()->randomElement([1, 3, 5]),
            'timeout' => fake()->randomElement([10, 30, 60]),
            'success_count' => fake()->numberBetween(0, 500),
            'failure_count' => fake()->numberBetween(0, 50),
            'last_called_at' => fake()->boolean(70) ? now()->subHours(fake()->numberBetween(1, 72)) : null,
        ];
    }

    /**
     * Indicate that the webhook is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the webhook has many successful calls.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'success_count' => fake()->numberBetween(1000, 5000),
            'failure_count' => fake()->numberBetween(0, 10),
        ]);
    }
}
