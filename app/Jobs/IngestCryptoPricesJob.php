<?php

namespace App\Jobs;

use App\Models\PriceTick;
use App\Services\Prices\CoinGeckoClient;

class IngestCryptoPricesJob
{
    public function __construct(private array $symbols)
    {
    }

    public function handle(CoinGeckoClient $client): void
    {
        foreach ($this->symbols as $symbol) {
            $data = $client->fetch($symbol);
            PriceTick::updateOrCreate(
                ['symbol' => $symbol, 'date' => $data['date']],
                ['price' => $data['price']]
            );
        }
    }
}
