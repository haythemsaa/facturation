<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('DEP-###')),
            'name' => fake()->randomElement(['Dépôt Principal', 'Dépôt Secondaire', 'Magasin']) . ' ' . fake()->city(),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Monastir', 'Nabeul', 'Bizerte', 'Gabès', 'Ariana']),
            'postal_code' => fake()->numerify('####'),
            'phone' => fake()->optional()->numerify('+216 ## ### ###'),
            'is_active' => true,
        ];
    }
}
