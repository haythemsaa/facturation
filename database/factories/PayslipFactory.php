<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payslip>
 */
class PayslipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseSalary = fake()->randomFloat(3, 600, 5000);
        $grossSalary = $baseSalary + fake()->randomFloat(3, 0, 500);
        $totalEarnings = $grossSalary + fake()->randomFloat(3, 0, 200);

        // Calculs approximatifs (sera recalculé par PayrollCalculator dans seeder)
        $cnssEmployee = round($grossSalary * 0.0918, 3);
        $cnssEmployer = round($grossSalary * 0.1657, 3);
        $irpp = round($grossSalary * 0.15, 3); // Approximatif
        $css = round($grossSalary * 0.01, 3);
        $tfp = round($grossSalary * 0.01, 3);
        $foprolos = round($grossSalary * 0.01, 3);

        $totalDeductions = $cnssEmployee + $irpp + $css + $tfp + $foprolos;
        $netSalary = $totalEarnings - $totalDeductions;

        return [
            'month' => fake()->numberBetween(1, 12),
            'year' => fake()->randomElement([2024, 2025]),
            'payment_date' => fake()->dateTimeThisYear(),
            'base_salary' => $baseSalary,
            'gross_salary' => $grossSalary,
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'cnss_employee' => $cnssEmployee,
            'cnss_employer' => $cnssEmployer,
            'irpp' => $irpp,
            'css' => $css,
            'tfp' => $tfp,
            'foprolos' => $foprolos,
            'net_salary' => $netSalary,
            'worked_days' => fake()->numberBetween(20, 26),
            'worked_hours' => fake()->randomFloat(2, 160, 208),
            'overtime_hours' => fake()->optional()->randomFloat(2, 0, 40),
            'status' => fake()->randomElement(['draft', 'validated', 'paid']),
            'details' => null,
        ];
    }
}
