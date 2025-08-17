<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PriceTick>
 */
class PriceTickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => strtoupper($this->faker->lexify('???')),
            'date' => $this->faker->date(),
            'price' => $this->faker->randomFloat(4, 10, 500),
        ];
    }
}
