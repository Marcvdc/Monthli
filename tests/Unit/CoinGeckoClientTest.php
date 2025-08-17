<?php

namespace Tests\Unit;

use App\Services\Prices\CoinGeckoClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoinGeckoClientTest extends TestCase
{
    public function test_fetch_returns_price_and_date(): void
    {
        Carbon::setTestNow('2024-01-01');
        Http::fake([
            '*' => Http::response([
                'bitcoin' => ['usd' => 12345.67],
            ], 200),
        ]);

        $client = new CoinGeckoClient;
        $data = $client->fetch('bitcoin');

        $this->assertSame('2024-01-01', $data['date']);
        $this->assertSame(12345.67, $data['price']);
    }
}
