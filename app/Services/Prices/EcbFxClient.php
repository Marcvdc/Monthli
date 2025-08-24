<?php

namespace App\Services\Prices;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class EcbFxClient
{
    /**
     * @return array<string, mixed>
     */
    public function fetch(string $base, string $quote): array
    {
        $key = "ecb:{$base}:{$quote}";

        if (RateLimiter::tooManyAttempts($key, 60)) {
            throw new RuntimeException('Rate limit exceeded');
        }

        RateLimiter::hit($key, 60);

        $url = 'https://api.exchangerate.host/latest';

        return retry([100, 200, 400], function () use ($url, $base, $quote) {
            $response = Http::get($url, [
                'base' => $base,
                'symbols' => $quote,
            ]);

            if ($response->failed()) {
                $response->throw();
            }

            $rate = $response->json("rates.$quote");
            $date = $response->json('date');

            return [
                'date' => $date,
                'rate' => $rate,
            ];
        });
    }
}
