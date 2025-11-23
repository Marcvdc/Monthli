<?php

namespace App\Jobs;

use App\Models\FxTick;
use App\Services\Prices\EcbFxClient;

class IngestFxRatesJob
{
    /**
     * @param  array<string>  $quotes
     */
    public function __construct(private array $quotes, private string $base = 'EUR') {}

    public function handle(EcbFxClient $client): void
    {
        foreach ($this->quotes as $quote) {
            $data = $client->fetch($this->base, $quote);
            FxTick::updateOrCreate(
                [
                    'base_currency' => $this->base,
                    'quote_currency' => $quote,
                    'date' => $data['date'],
                ],
                ['rate' => $data['rate']]
            );
        }
    }
}
