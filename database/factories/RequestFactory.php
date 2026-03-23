<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['solicitado', 'preparando', 'enviando', 'recebido', 'entregue'];
        $reasons = ['Desgaste', 'Erro de Fabrico', 'Outro'];

        return [
            'item_id' => Item::factory(),
            'reference' => 'REF-'.fake()->numberBetween(1000, 9999),
            'work_order' => 'WO-'.fake()->numberBetween(10000, 99999),
            'quantity' => fake()->numberBetween(1, 50),
            'equipment_id' => 'EQ-'.fake()->numberBetween(100, 999),
            'replacement_reason' => fake()->randomElement($reasons),
            'status' => fake()->randomElement($statuses),
            'requester_id' => User::factory(),
            'notes' => fake()->optional()->sentence(),
            'requested_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the request is solicitado.
     */
    public function solicitado(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'solicitado',
        ]);
    }

    /**
     * Indicate that the request is entregue.
     */
    public function entregue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'entregue',
            'completed_at' => fake()->dateTimeBetween($attributes['requested_at'], 'now'),
        ]);
    }
}
