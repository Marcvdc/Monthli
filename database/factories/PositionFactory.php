<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
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
            'symbol' => strtoupper($this->faker->lexify('???')),
            'quantity' => $this->faker->randomFloat(4, 1, 100),
            'average_price' => $this->faker->randomFloat(4, 10, 200),
            'currency' => 'USD',
        ];
    }
}
