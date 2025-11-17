<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('FRS-####')),
            'name' => fake()->company(),
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->numerify('+216 ## ### ###'),
            'mobile' => fake()->optional()->numerify('+216 ## ### ###'),
            'fax' => fake()->optional()->numerify('+216 ## ### ###'),
            'website' => fake()->optional()->domainName(),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Monastir', 'Nabeul', 'Bizerte', 'Gabès', 'Ariana']),
            'postal_code' => fake()->numerify('####'),
            'country' => 'Tunisie',
            'tax_id' => fake()->numerify('#######') . fake()->randomLetter() . fake()->randomLetter() . fake()->randomLetter(),
            'payment_terms' => fake()->randomElement(['cash', '30_days', '60_days', '90_days']),
            'is_active' => true,
            'note' => fake()->optional()->sentence(),
        ];
    }
}
