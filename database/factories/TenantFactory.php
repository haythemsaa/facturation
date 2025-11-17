<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'name' => $companyName,
            'slug' => Str::slug($companyName) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('+216 ## ### ###'),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Monastir', 'Nabeul', 'Bizerte', 'Gabès', 'Ariana']),
            'postal_code' => fake()->numerify('####'),
            'country' => 'Tunisie',
            'matricule_fiscal' => fake()->unique()->numerify('#######') . fake()->randomLetter() . fake()->randomLetter() . fake()->randomLetter(),
            'code_tva' => 'TN' . fake()->unique()->numerify('########'),
            'rne' => fake()->optional()->numerify('########'),
            'logo' => null,
            'settings' => [
                'timezone' => 'Africa/Tunis',
                'currency' => 'TND',
                'language' => 'fr',
                'date_format' => 'd/m/Y',
            ],
            'is_active' => true,
        ];
    }
}
