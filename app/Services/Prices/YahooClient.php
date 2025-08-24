<?php

namespace App\Services\Prices;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class YahooClient
{
    /**
     * @return array<string, mixed>
     */
    public function fetch(string $symbol): array
    {
        $key = "yahoo:{$symbol}";

        if (RateLimiter::tooManyAttempts($key, 60)) {
            throw new RuntimeException('Rate limit exceeded');
        }

        RateLimiter::hit($key, 60);

        $url = "https://query1.finance.yahoo.com/v8/finance/chart/{$symbol}";

        return retry([100, 200, 400], function () use ($url) {
            $response = Http::get($url, [
                'interval' => '1d',
                'range' => '1d',
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $result = $response->json('chart.result.0');
            $timestamp = $result['timestamp'][0];
            $price = $result['indicators']['adjclose'][0]['adjclose'][0];

            return [
                'date' => Carbon::createFromTimestamp($timestamp)->toDateString(),
                'price' => $price,
            ];
        });
    }
}
