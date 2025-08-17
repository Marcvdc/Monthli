<?php

namespace Tests\Unit;

use App\Services\Prices\EcbFxClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EcbFxClientTest extends TestCase
{
    public function test_fetch_returns_rate_and_date(): void
    {
        Http::fake([
            '*' => Http::response([
                'date' => '2024-01-01',
                'rates' => ['USD' => 1.1],
            ], 200),
        ]);

        $client = new EcbFxClient();
        $data = $client->fetch('EUR', 'USD');

        $this->assertSame('2024-01-01', $data['date']);
        $this->assertSame(1.1, $data['rate']);
    }
}
