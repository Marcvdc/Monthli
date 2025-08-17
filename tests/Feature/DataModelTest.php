<?php

namespace Tests\Feature;

use App\Models\MonthlySnapshot;
use App\Models\Portfolio;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_related_models(): void
    {
        $this->seed();

        $portfolio = Portfolio::first();
        $this->assertNotNull($portfolio);
        $this->assertNotNull($portfolio->user);
        $this->assertCount(1, $portfolio->positions);
        $this->assertCount(1, $portfolio->transactions);
        $this->assertCount(1, $portfolio->dividends);
        $this->assertCount(1, $portfolio->monthlySnapshots);
    }

    public function test_monthly_snapshot_is_unique_per_portfolio_and_month(): void
    {
        $this->seed();
        $snapshot = Portfolio::first()->monthlySnapshots()->first();

        $this->expectException(UniqueConstraintViolationException::class);
        MonthlySnapshot::create([
            'portfolio_id' => $snapshot->portfolio_id,
            'month' => $snapshot->month,
            'value' => 123.45,
        ]);
    }
}
