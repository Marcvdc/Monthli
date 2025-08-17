<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthlySnapshot>
 */
class MonthlySnapshotFactory extends Factory
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
            'month' => $this->faker->date(),
            'value' => $this->faker->randomFloat(4, 1000, 100000),
        ];
    }
}
