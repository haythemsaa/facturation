<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['lead', 'prospect', 'customer']);

        return [
            'type' => $type,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => fake()->optional()->company(),
            'position' => fake()->optional()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+216 ## ### ###'),
            'mobile' => fake()->optional()->numerify('+216 ## ### ###'),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->randomElement(['Tunis', 'Sfax', 'Sousse', 'Monastir', 'Nabeul', 'Bizerte']),
            'country' => 'Tunisie',
            'source' => fake()->randomElement(['website', 'referral', 'social_media', 'cold_call', 'event', 'other']),
            'status' => fake()->randomElement(['new', 'contacted', 'qualified', 'lost']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
