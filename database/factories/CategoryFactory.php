<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Électronique', 'Informatique', 'Fournitures de Bureau', 'Mobilier',
            'Alimentaire', 'Textile', 'Produits Chimiques', 'Matériel Médical',
            'Pièces Auto', 'Quincaillerie', 'Cosmétiques', 'Papeterie'
        ];

        return [
            'name' => fake()->unique()->randomElement($categories),
            'code' => strtoupper(fake()->lexify('???-###')),
            'description' => fake()->optional()->sentence(),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}
