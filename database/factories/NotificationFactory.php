<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'invoice_paid' => [
                'title' => 'Facture payée',
                'message' => 'La facture #{number} a été payée avec succès',
                'icon' => 'check-circle',
                'priority' => 'normal',
            ],
            'low_stock' => [
                'title' => 'Stock faible',
                'message' => 'Le produit {product} est en stock faible ({quantity} restants)',
                'icon' => 'alert-triangle',
                'priority' => 'high',
            ],
            'opportunity_won' => [
                'title' => 'Opportunité gagnée',
                'message' => 'L\'opportunité {name} a été marquée comme gagnée ({amount} TND)',
                'icon' => 'trophy',
                'priority' => 'normal',
            ],
            'subscription_expiring' => [
                'title' => 'Abonnement expire bientôt',
                'message' => 'Votre abonnement expire dans {days} jours',
                'icon' => 'calendar',
                'priority' => 'urgent',
            ],
            'new_lead' => [
                'title' => 'Nouveau lead',
                'message' => 'Un nouveau lead {name} a été créé',
                'icon' => 'user-plus',
                'priority' => 'normal',
            ],
        ];

        $type = fake()->randomElement(array_keys($types));
        $config = $types[$type];

        return [
            'id' => fake()->uuid(),
            'type' => $type,
            'title' => $config['title'],
            'message' => $config['message'],
            'data' => json_encode([
                'icon' => $config['icon'],
                'timestamp' => now()->toISOString(),
            ]),
            'action_url' => fake()->boolean(70) ? fake()->randomElement([
                '/invoices/123',
                '/products/456',
                '/opportunities/789',
                '/settings/subscription',
                '/contacts/321',
            ]) : null,
            'priority' => $config['priority'],
            'read_at' => fake()->boolean(30) ? now()->subHours(fake()->numberBetween(1, 48)) : null,
            'created_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ]);
    }

    /**
     * Indicate that the notification is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }
}
