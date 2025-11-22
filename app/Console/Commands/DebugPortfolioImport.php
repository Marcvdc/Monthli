<?php

namespace App\Console\Commands;

use App\Jobs\ImportStartingBalanceJob;
use App\Models\Portfolio;
use App\Models\Position;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DebugPortfolioImport extends Command
{
    protected $signature = 'debug:portfolio-import {portfolio_id} {--csv=}';
    protected $description = 'Debug portfolio import issues';

    public function handle(): int
    {
        $portfolioId = $this->argument('portfolio_id');
        $csvPath = $this->option('csv');

        $portfolio = Portfolio::find($portfolioId);
        if (!$portfolio) {
            $this->error("Portfolio with ID {$portfolioId} not found");
            return 1;
        }

        $this->info("🔍 Debugging Portfolio Import for: {$portfolio->name}");
        
        // Show current state
        $this->info("📊 Current Portfolio State:");
        $this->info("- Balance Date: " . ($portfolio->balance_date ? $portfolio->balance_date->format('Y-m-d') : 'null'));
        $positionCount = Position::where('portfolio_id', $portfolio->id)->count();
        $this->info("- Positions: {$positionCount}");

        if ($positionCount > 0) {
            $positions = Position::where('portfolio_id', $portfolio->id)->get();
            foreach ($positions as $position) {
                $this->info("  - {$position->symbol}: {$position->quantity} @ €{$position->average_price}");
            }
        }

        // Test with sample CSV if provided
        if ($csvPath && file_exists($csvPath)) {
            $this->info("\n🧪 Testing with CSV: {$csvPath}");
            
            try {
                $job = new ImportStartingBalanceJob(
                    $csvPath,
                    $portfolio->id,
                    today()->format('Y-m-d'),
                    'debug'
                );

                $this->info("🔄 Running import job...");
                $job->handle(app(\App\Services\StartingBalanceImportService::class));
                
                // Check results
                $portfolio->refresh();
                $newPositionCount = Position::where('portfolio_id', $portfolio->id)->count();
                
                $this->info("✅ Import completed!");
                $this->info("- New Balance Date: " . ($portfolio->balance_date ? $portfolio->balance_date->format('Y-m-d') : 'null'));
                $this->info("- Positions after import: {$newPositionCount}");
                
                if ($newPositionCount > $positionCount) {
                    $newPositions = Position::where('portfolio_id', $portfolio->id)->get();
                    $this->info("📈 New Positions:");
                    foreach ($newPositions as $position) {
                        $this->info("  - {$position->symbol} ({$position->isin}): {$position->quantity} @ €{$position->average_price}");
                    }
                }

            } catch (\Exception $e) {
                $this->error("❌ Import failed: " . $e->getMessage());
                $this->error($e->getTraceAsString());
                return 1;
            }
        } else {
            $this->warn("No CSV file provided. Use --csv=/path/to/file.csv to test import");
        }

        return 0;
    }
}
