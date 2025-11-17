<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positions = [
            'Directeur Général', 'Directeur Commercial', 'Responsable Marketing',
            'Chef Comptable', 'Responsable RH', 'Développeur', 'Commercial',
            'Assistant(e)', 'Technicien', 'Agent de Maîtrise', 'Cadre'
        ];

        return [
            'title' => fake()->randomElement($positions),
            'code' => strtoupper(fake()->unique()->bothify('POS-###')),
            'description' => fake()->optional()->sentence(),
            'level' => fake()->randomElement(['junior', 'intermediate', 'senior', 'manager', 'director']),
            'is_active' => true,
        ];
    }
}
