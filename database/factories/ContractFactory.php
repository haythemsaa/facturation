<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['CDI', 'CDD', 'SIVP', 'Karama', 'Stage']);
        $startDate = fake()->dateTimeBetween('-5 years', 'now');
        $baseSalary = fake()->randomFloat(3, 600, 5000);

        return [
            'contract_number' => strtoupper(fake()->unique()->bothify('CTR-####-??')),
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => in_array($type, ['CDD', 'SIVP', 'Karama', 'Stage'])
                ? fake()->dateTimeBetween($startDate, '+2 years')
                : null,
            'base_salary' => $baseSalary,
            'gross_salary' => $baseSalary,
            'work_hours_per_week' => fake()->randomElement([40, 48]),
            'paid_leave_days' => fake()->randomElement([12, 15, 18, 24]),
            'probation_period_months' => fake()->randomElement([1, 3, 6]),
            'notice_period_days' => fake()->randomElement([15, 30, 60]),
            'status' => 'active',
            'details' => null,
        ];
    }
}
