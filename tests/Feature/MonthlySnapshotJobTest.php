<?php

namespace Tests\Feature;

use App\Jobs\MakeMonthlySnapshotJob;
use App\Models\FxTick;
use App\Models\MonthlySnapshot;
use App\Models\Portfolio;
use App\Models\Position;
use App\Models\PriceTick;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlySnapshotJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_monthly_snapshot_job_calculates_metrics(): void
    {
        $portfolio = Portfolio::factory()->create();
        Position::factory()->for($portfolio)->create([
            'symbol' => 'ACME',
            'quantity' => 2,
            'average_price' => 100,
            'currency' => 'USD',
        ]);

        PriceTick::create(['symbol' => 'ACME', 'date' => '2024-01-31', 'price' => 100]);
        PriceTick::create(['symbol' => 'ACME', 'date' => '2024-02-29', 'price' => 110]);
        FxTick::create(['base_currency' => 'USD', 'quote_currency' => 'EUR', 'date' => '2024-01-31', 'rate' => 0.9]);
        FxTick::create(['base_currency' => 'USD', 'quote_currency' => 'EUR', 'date' => '2024-02-29', 'rate' => 0.8]);

        MonthlySnapshot::create([
            'portfolio_id' => $portfolio->id,
            'month' => '2024-01-01',
            'value' => 180,
        ]);

        (new MakeMonthlySnapshotJob($portfolio, Carbon::parse('2024-02-29')))->handle();

        $snapshot = MonthlySnapshot::where('portfolio_id', $portfolio->id)
            ->whereDate('month', '2024-02-01')
            ->first();

        $this->assertNotNull($snapshot);
        $this->assertEqualsWithDelta(176, $snapshot->value, 0.0001);
        $expected = (176 - 180) / 180;
        $this->assertEqualsWithDelta($expected, $snapshot->mom, 0.0001);
        $this->assertEqualsWithDelta($expected, $snapshot->ytd, 0.0001);
        $this->assertEqualsWithDelta($expected, $snapshot->drawdown, 0.0001);
        $this->assertEquals(0.0, $snapshot->volatility);
    }

    public function test_snapshots_backfill_command_creates_snapshots_in_range(): void
    {
        $portfolio = Portfolio::factory()->create();
        Position::factory()->for($portfolio)->create([
            'symbol' => 'ACME',
            'quantity' => 1,
            'average_price' => 100,
            'currency' => 'USD',
        ]);

        PriceTick::create(['symbol' => 'ACME', 'date' => '2024-01-31', 'price' => 100]);
        PriceTick::create(['symbol' => 'ACME', 'date' => '2024-02-29', 'price' => 110]);
        FxTick::create(['base_currency' => 'USD', 'quote_currency' => 'EUR', 'date' => '2024-01-31', 'rate' => 0.9]);
        FxTick::create(['base_currency' => 'USD', 'quote_currency' => 'EUR', 'date' => '2024-02-29', 'rate' => 0.8]);

        $this->artisan('snapshots:backfill', [
            'from' => '2024-01',
            'to' => '2024-02',
            '--portfolio' => $portfolio->id,
        ])->assertExitCode(0);

        $this->assertEquals(2, $portfolio->monthlySnapshots()->count());
    }
}
