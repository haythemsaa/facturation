<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = fake()->randomElement(['starter', 'business', 'enterprise']);

        $modulesMap = [
            'starter' => ['stock'],
            'business' => ['stock', 'crm'],
            'enterprise' => ['stock', 'crm', 'hr'],
        ];

        $priceMap = [
            'starter' => '99.000',
            'business' => '249.000',
            'enterprise' => '499.000',
        ];

        $maxUsersMap = [
            'starter' => 3,
            'business' => 10,
            'enterprise' => null,
        ];

        return [
            'plan' => $plan,
            'modules' => $modulesMap[$plan],
            'max_users' => $maxUsersMap[$plan],
            'max_storage_gb' => $plan === 'enterprise' ? null : ($plan === 'business' ? 50 : 10),
            'price' => $priceMap[$plan],
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'status' => 'active',
            'trial_ends_at' => null,
            'starts_at' => now()->subMonths(fake()->numberBetween(1, 6)),
            'ends_at' => now()->addMonths(fake()->numberBetween(6, 12)),
        ];
    }
}
