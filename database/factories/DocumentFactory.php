<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['quote', 'invoice', 'delivery_note', 'purchase_order', 'receipt']);
        $date = fake()->dateTimeBetween('-6 months', 'now');
        $subtotal = fake()->randomFloat(3, 100, 10000);
        $discountRate = fake()->randomFloat(2, 0, 15);
        $discountAmount = round($subtotal * ($discountRate / 100), 3);
        $totalHT = $subtotal - $discountAmount;
        $tvaRate = fake()->randomElement(['19', '13', '7', '0']);
        $totalTVA = round($totalHT * (floatval($tvaRate) / 100), 3);
        $timbreFiscal = in_array($type, ['invoice', 'credit_note']) ? min(round($totalHT * 0.01, 3), 1.000) : 0;
        $totalTTC = $totalHT + $totalTVA + $timbreFiscal;

        return [
            'type' => $type,
            'number' => strtoupper(fake()->unique()->bothify('DOC-####-??')),
            'date' => $date,
            'due_date' => $type === 'invoice' ? fake()->dateTimeBetween($date, '+90 days') : null,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_rate' => $discountRate,
            'total_ht' => $totalHT,
            'total_tva' => $totalTVA,
            'timbre_fiscal' => $timbreFiscal,
            'total_ttc' => $totalTTC,
            'status' => fake()->randomElement(['draft', 'sent', 'validated', 'cancelled']),
            'note' => fake()->optional()->sentence(),
            'terms' => fake()->optional()->paragraph(),
        ];
    }
}
