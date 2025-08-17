<?php

namespace Tests\Unit;

use App\Models\Symbol;
use App\Services\Symbols\Resolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymbolResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_ticker_and_isin(): void
    {
        Symbol::create(['isin' => 'US0000001', 'ticker' => 'TICK']);
        $resolver = new Resolver();

        $this->assertSame('TICK', $resolver->ticker('US0000001'));
        $this->assertSame('US0000001', $resolver->isin('TICK'));
    }
}
