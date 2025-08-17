<?php

namespace Tests\Feature;

use App\Jobs\IngestCryptoPricesJob;
use App\Jobs\IngestEquityPricesJob;
use App\Jobs\IngestFxRatesJob;
use App\Models\FxTick;
use App\Models\PriceTick;
use App\Services\Prices\CoinGeckoClient;
use App\Services\Prices\EcbFxClient;
use App\Services\Prices\YahooClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IngestJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_equity_job_stores_price_tick(): void
    {
        $timestamp = Carbon::parse('2024-01-01')->timestamp;
        Http::fake([
            '*' => Http::response([
                'chart' => [
                    'result' => [[
                        'timestamp' => [$timestamp],
                        'indicators' => [
                            'adjclose' => [[
                                'adjclose' => [10],
                            ]],
                        ],
                    ]],
                ],
            ], 200),
        ]);

        $job = new IngestEquityPricesJob(['DUMMY']);
        $job->handle(new YahooClient);

        $tick = PriceTick::first();
        $this->assertNotNull($tick);
        $this->assertSame('DUMMY', $tick->symbol);
        $this->assertEquals(10, $tick->price);
    }

    public function test_crypto_job_stores_price_tick(): void
    {
        Carbon::setTestNow('2024-01-01');
        Http::fake([
            '*' => Http::response([
                'bitcoin' => ['usd' => 20],
            ], 200),
        ]);

        $job = new IngestCryptoPricesJob(['bitcoin']);
        $job->handle(new CoinGeckoClient);

        $tick = PriceTick::first();
        $this->assertSame('bitcoin', $tick->symbol);
        $this->assertEquals(20, $tick->price);
    }

    public function test_fx_job_stores_fx_tick(): void
    {
        Http::fake([
            '*' => Http::response([
                'date' => '2024-01-01',
                'rates' => ['USD' => 1.2],
            ], 200),
        ]);

        $job = new IngestFxRatesJob(['USD']);
        $job->handle(new EcbFxClient);

        $tick = FxTick::first();
        $this->assertSame('EUR', $tick->base_currency);
        $this->assertSame('USD', $tick->quote_currency);
        $this->assertEquals(1.2, $tick->rate);
    }
}
