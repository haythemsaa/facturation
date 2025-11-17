<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departments = [
            'Direction Générale', 'Commercial', 'Marketing', 'Finance',
            'Ressources Humaines', 'IT', 'Production', 'Logistique',
            'Service Client', 'Qualité'
        ];

        return [
            'name' => fake()->unique()->randomElement($departments),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
