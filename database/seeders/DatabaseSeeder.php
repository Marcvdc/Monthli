<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\{Dividend, FxTick, MonthlySnapshot, Portfolio, Position, PriceTick, Transaction};
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create();

        $portfolio = Portfolio::factory()->for($user)->create();

        $position = Position::factory()->for($portfolio)->create([
            'symbol' => 'ACME',
            'quantity' => 10,
            'average_price' => 100,
            'currency' => 'USD',
        ]);

        Transaction::factory()->for($portfolio)->for($position)->create();
        Dividend::factory()->for($portfolio)->create();
        MonthlySnapshot::factory()->for($portfolio)->create([
            'month' => now()->startOfMonth(),
            'value' => 1000,
        ]);

        PriceTick::factory()->create([
            'symbol' => 'ACME',
            'date' => now()->endOfMonth(),
            'price' => 110,
        ]);

        FxTick::factory()->create([
            'base_currency' => 'USD',
            'quote_currency' => 'EUR',
            'date' => now()->endOfMonth(),
            'rate' => 0.9,
        ]);
    }
}
