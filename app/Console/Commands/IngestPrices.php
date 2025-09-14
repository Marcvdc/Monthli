<?php

namespace App\Console\Commands;

use App\Jobs\IngestEquityPricesJob;
use App\Models\Position;
use App\Models\PriceTick;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IngestPrices extends Command
{
    protected $signature = 'prices:ingest {--symbol=} {--all} {--force}';
    protected $description = 'Ingest current prices for portfolio symbols';

    public function handle(): int
    {
        $this->info('📈 Starting Price Ingestion');
        
        if ($this->option('symbol')) {
            $symbols = [$this->option('symbol')];
        } elseif ($this->option('all')) {
            // Get all unique symbols from positions
            $symbols = Position::whereNotNull('symbol')
                ->distinct()
                ->pluck('symbol')
                ->toArray();
        } else {
            // Default: get symbols that don't have today's price yet
            $today = today()->format('Y-m-d');
            $symbolsWithPrices = PriceTick::where('date', $today)
                ->pluck('symbol')
                ->toArray();
                
            $symbols = Position::whereNotNull('symbol')
                ->distinct()
                ->pluck('symbol')
                ->reject(fn($symbol) => in_array($symbol, $symbolsWithPrices) && !$this->option('force'))
                ->toArray();
        }

        if (empty($symbols)) {
            $this->info('✅ All symbols already have current prices (use --force to refresh)');
            return 0;
        }

        $this->info("🔍 Processing " . count($symbols) . " symbols: " . implode(', ', $symbols));

        $successCount = 0;
        $errorCount = 0;

        foreach ($symbols as $symbol) {
            try {
                $this->info("Fetching {$symbol}...");
                
                // Run job synchronously for immediate feedback
                $job = new IngestEquityPricesJob([$symbol]);
                $job->handle(app(\App\Services\Prices\YahooClient::class));
                
                // Verify price was saved
                $latestPrice = PriceTick::where('symbol', $symbol)
                    ->latest('date')
                    ->first();
                
                if ($latestPrice) {
                    $this->info("✅ {$symbol}: €{$latestPrice->price} ({$latestPrice->date})");
                    $successCount++;
                } else {
                    $this->warn("⚠️  No price data saved for {$symbol}");
                    $errorCount++;
                }
                
                // Rate limiting - small delay between requests
                usleep(250000); // 250ms delay
                
            } catch (\Exception $e) {
                $this->error("❌ {$symbol}: " . $e->getMessage());
                $errorCount++;
                Log::error("Price ingestion failed for {$symbol}", [
                    'symbol' => $symbol,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
        $this->info("📊 Price Ingestion Summary:");
        $this->info("- Success: {$successCount}");
        $this->info("- Errors: {$errorCount}");
        $this->info("- Total symbols processed: " . count($symbols));

        if ($successCount > 0) {
            $totalPrices = PriceTick::count();
            $uniqueSymbols = PriceTick::distinct('symbol')->count();
            $this->info("- Total price records: {$totalPrices}");
            $this->info("- Unique symbols tracked: {$uniqueSymbols}");
        }

        return $errorCount > 0 ? 1 : 0;
    }
}
