<?php

namespace App\Console\Commands;

use App\Jobs\IngestEquityPricesJob;
use App\Models\Position;
use App\Models\PriceTick;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestPriceIngestion extends Command
{
    protected $signature = 'test:price-ingestion {--symbol=} {--all}';
    protected $description = 'Test price ingestion for portfolio symbols';

    public function handle(): int
    {
        $this->info('🔄 Testing Price Ingestion System');
        
        if ($this->option('symbol')) {
            $symbols = [$this->option('symbol')];
        } elseif ($this->option('all')) {
            // Get all unique symbols from positions
            $symbols = Position::whereNotNull('symbol')
                ->distinct()
                ->pluck('symbol')
                ->toArray();
        } else {
            // Default: get first few symbols for testing
            $symbols = Position::whereNotNull('symbol')
                ->distinct()
                ->limit(3)
                ->pluck('symbol')
                ->toArray();
        }

        if (empty($symbols)) {
            $this->error('No symbols found in positions. Import some portfolio data first.');
            return 1;
        }

        $this->info("Testing with symbols: " . implode(', ', $symbols));

        try {
            foreach ($symbols as $symbol) {
                $this->info("🔍 Fetching price for: {$symbol}");
                
                $job = new IngestEquityPricesJob([$symbol]);
                $job->handle(app(\App\Services\Prices\YahooClient::class));
                
                // Check if price was saved
                $latestPrice = PriceTick::where('symbol', $symbol)
                    ->latest('date')
                    ->first();
                
                if ($latestPrice) {
                    $this->info("✅ {$symbol}: €{$latestPrice->price} ({$latestPrice->date})");
                } else {
                    $this->warn("⚠️  No price data saved for {$symbol}");
                }
            }

            $this->newLine();
            $this->info('📊 Current Price Data Summary:');
            $priceCount = PriceTick::count();
            $symbolCount = PriceTick::distinct('symbol')->count();
            $this->info("- Total price records: {$priceCount}");
            $this->info("- Unique symbols: {$symbolCount}");
            
            if ($priceCount > 0) {
                $latestDate = PriceTick::max('date');
                $this->info("- Latest price date: {$latestDate}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Error during price ingestion: " . $e->getMessage());
            Log::error('Price ingestion test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        $this->newLine();
        $this->info('✅ Price ingestion test completed successfully!');
        return 0;
    }
}
