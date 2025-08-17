<?php

namespace Tests\Unit;

use App\Services\Prices\YahooClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YahooClientTest extends TestCase
{
    public function test_fetch_returns_price_and_date(): void
    {
        $timestamp = Carbon::parse('2024-01-01')->timestamp;
        Http::fake([
            '*' => Http::response([
                'chart' => [
                    'result' => [[
                        'timestamp' => [$timestamp],
                        'indicators' => [
                            'adjclose' => [[
                                'adjclose' => [123.45],
                            ]],
                        ],
                    ]],
                ],
            ], 200),
        ]);

        $client = new YahooClient;
        $data = $client->fetch('DUMMY');

        $this->assertSame('2024-01-01', $data['date']);
        $this->assertSame(123.45, $data['price']);
    }
}
