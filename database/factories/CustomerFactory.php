<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['individual', 'company']);
        $isCompany = $type === 'company';

        return [
            'type' => $type,
            'code' => strtoupper(fake()->unique()->bothify('CLI-####')),
            'name' => $isCompany ? fake()->company() : fake()->name(),
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->numerify('+216 ## ### ###'),
            'mobile' => fake()->optional()->numerify('+216 ## ### ###'),
            'fax' => fake()->optional()->numerify('+216 ## ### ###'),
            'website' => $isCompany ? fake()->optional()->domainName() : null,
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Monastir', 'Nabeul', 'Bizerte', 'Gabès', 'Ariana']),
            'postal_code' => fake()->numerify('####'),
            'country' => 'Tunisie',
            'tax_id' => $isCompany ? fake()->numerify('#######') . fake()->randomLetter() . fake()->randomLetter() . fake()->randomLetter() : null,
            'payment_terms' => fake()->randomElement(['cash', '30_days', '60_days', '90_days']),
            'credit_limit' => fake()->optional()->randomFloat(3, 1000, 50000),
            'discount_rate' => fake()->optional()->randomFloat(2, 0, 15),
            'is_active' => true,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
