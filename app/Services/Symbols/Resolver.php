<?php

namespace App\Services\Symbols;

use App\Models\Symbol;

class Resolver
{
    public function ticker(string $isin): ?string
    {
        return Symbol::where('isin', $isin)->value('ticker');
    }

    public function isin(string $ticker): ?string
    {
        return Symbol::where('ticker', $ticker)->value('isin');
    }
}
