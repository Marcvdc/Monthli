<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'portfolio_id' => \App\Models\Portfolio::factory(),
            'position_id' => \App\Models\Position::factory(),
            'type' => $this->faker->randomElement(['buy', 'sell']),
            'quantity' => $this->faker->randomFloat(4, 1, 100),
            'price' => $this->faker->randomFloat(4, 10, 200),
            'executed_at' => $this->faker->dateTime(),
            'external_id' => $this->faker->uuid(),
        ];
    }
}
