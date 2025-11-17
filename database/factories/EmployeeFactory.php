<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'employee_number' => strtoupper(fake()->unique()->bothify('EMP-####')),
            'first_name' => fake()->firstName($gender === 'male' ? 'male' : 'female'),
            'last_name' => fake()->lastName(),
            'cin' => fake()->unique()->numerify('########'),
            'cnss_number' => fake()->unique()->numerify('##########'),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-22 years'),
            'birth_place' => fake()->city(),
            'gender' => $gender,
            'marital_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'children_count' => fake()->numberBetween(0, 5),
            'is_family_head' => fake()->boolean(40),
            'nationality' => 'Tunisienne',
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Monastir', 'Nabeul', 'Bizerte']),
            'postal_code' => fake()->numerify('####'),
            'phone' => fake()->numerify('+216 ## ### ###'),
            'mobile' => fake()->numerify('+216 ## ### ###'),
            'email' => fake()->unique()->safeEmail(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('+216 ## ### ###'),
            'hire_date' => fake()->dateTimeBetween('-10 years', 'now'),
            'status' => 'active',
            'photo' => null,
            'metadata' => null,
        ];
    }
}
