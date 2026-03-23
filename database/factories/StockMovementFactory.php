<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movementTypes = ['entrada', 'saida'];
        $type = fake()->randomElement($movementTypes);

        $reasons = $type === 'entrada'
            ? ['compra', 'devolucao', 'outro']
            : ['pedido', 'consumo_interno', 'outro'];

        $previousStock = fake()->numberBetween(10, 100);
        $quantity = fake()->numberBetween(1, 20);
        $newStock = $type === 'entrada' ? $previousStock + $quantity : $previousStock - $quantity;

        return [
            'item_id' => Item::factory(),
            'movement_type' => $type,
            'movement_reason' => fake()->randomElement($reasons),
            'quantity' => $quantity,
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'reference' => 'REF-'.fake()->numberBetween(1000, 9999),
            'supplier' => $type === 'entrada' ? fake()->company() : null,
            'destination' => $type === 'saida' ? 'EQ-'.fake()->numberBetween(100, 999) : null,
            'location' => 'Prateleira '.fake()->randomElement(['A', 'B', 'C']).fake()->numberBetween(1, 20),
            'user_id' => User::factory(),
            'notes' => fake()->optional()->sentence(),
            'movement_date' => fake()->dateTimeBetween('-60 days', 'now'),
        ];
    }
}
