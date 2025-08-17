<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FxTick>
 */
class FxTickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'base_currency' => strtoupper($this->faker->lexify('???')),
            'quote_currency' => strtoupper($this->faker->lexify('???')),
            'date' => $this->faker->date(),
            'rate' => $this->faker->randomFloat(6, 0.1, 2.0),
        ];
    }
}
