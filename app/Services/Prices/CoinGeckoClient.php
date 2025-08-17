<?php

namespace App\Services\Prices;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class CoinGeckoClient
{
    public function fetch(string $symbol): array
    {
        $key = "coingecko:{$symbol}";

        if (RateLimiter::tooManyAttempts($key, 60)) {
            throw new RuntimeException('Rate limit exceeded');
        }

        RateLimiter::hit($key, 60);

        $url = 'https://api.coingecko.com/api/v3/simple/price';

        return retry([100, 200, 400], function () use ($url, $symbol) {
            $response = Http::get($url, [
                'ids' => $symbol,
                'vs_currencies' => 'usd',
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $price = $response->json("{$symbol}.usd");

            return [
                'date' => Carbon::now()->toDateString(),
                'price' => $price,
            ];
        });
    }
}
