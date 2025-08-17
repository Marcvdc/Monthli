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
        $position = Position::factory()->for($portfolio)->create();

        Transaction::factory()->for($portfolio)->for($position)->create();
        Dividend::factory()->for($portfolio)->create();
        MonthlySnapshot::factory()->for($portfolio)->create();

        PriceTick::factory()->create();
        FxTick::factory()->create();
    }
}
