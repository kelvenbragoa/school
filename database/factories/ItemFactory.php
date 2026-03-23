<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Elétrica', 'Mecânica', 'Hidráulica', 'Pneumática', 'Consumíveis', 'EPIs', 'Ferramentas'];
        $units = ['un', 'kg', 'm', 'L', 'cx', 'rolo', 'par'];

        $minStock = fake()->numberBetween(5, 50);
        $maxStock = $minStock * fake()->numberBetween(3, 10);

        return [
            'code' => strtoupper(fake()->unique()->bothify('???-####')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'unit' => fake()->randomElement($units),
            'category' => fake()->randomElement($categories),
            'location' => 'Prateleira '.fake()->randomElement(['A', 'B', 'C', 'D']).fake()->numberBetween(1, 20),
            'min_stock' => $minStock,
            'max_stock' => $maxStock,
            'current_stock' => fake()->numberBetween($minStock, $maxStock),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the item has low stock.
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'current_stock' => fake()->numberBetween(0, $attributes['min_stock']),
            ];
        });
    }

    /**
     * Indicate that the item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
