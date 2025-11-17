<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(3, 5, 500);
        $margin = fake()->randomFloat(2, 1.2, 2.5);
        $sellingPrice = round($purchasePrice * $margin, 3);

        return [
            'code' => strtoupper(fake()->unique()->bothify('PRD-####??')),
            'barcode' => fake()->optional()->ean13(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'type' => fake()->randomElement(['product', 'service', 'consumable']),
            'unit' => fake()->randomElement(['pièce', 'kg', 'litre', 'mètre', 'carton', 'palette']),
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'minimum_price' => round($purchasePrice * 1.1, 3),
            'tva_rate' => fake()->randomElement(['19', '13', '7', '0']),
            'stock_alert_threshold' => fake()->numberBetween(5, 50),
            'track_stock' => fake()->boolean(80),
            'is_active' => true,
            'image' => null,
            'metadata' => null,
        ];
    }
}
