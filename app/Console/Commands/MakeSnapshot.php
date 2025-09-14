<?php

namespace App\Console\Commands;

use App\Jobs\MakeMonthlySnapshotJob;
use App\Models\Portfolio;
use App\Models\MonthlySnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MakeSnapshot extends Command
{
    protected $signature = 'snapshot:make {--portfolio=} {--date=} {--all}';
    protected $description = 'Create monthly portfolio snapshots';

    public function handle(): int
    {
        $this->info('📊 Creating Monthly Portfolio Snapshots');

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $monthDate = $date->startOfMonth();
        $month = $monthDate->format('Y-m-d');

        if ($this->option('portfolio')) {
            $portfolios = Portfolio::where('id', $this->option('portfolio'))->get();
            if ($portfolios->isEmpty()) {
                $this->error("Portfolio {$this->option('portfolio')} not found");
                return 1;
            }
        } elseif ($this->option('all')) {
            $portfolios = Portfolio::all();
        } else {
            // Default: portfolios that don't have a snapshot for this month
            $portfoliosWithSnapshot = MonthlySnapshot::where('month', $month)
                ->pluck('portfolio_id')
                ->toArray();
                
            $portfolios = Portfolio::whereNotIn('id', $portfoliosWithSnapshot)->get();
        }

        if ($portfolios->isEmpty()) {
            $this->info("✅ All portfolios already have snapshots for {$month}");
            return 0;
        }

        $this->info("📈 Processing " . $portfolios->count() . " portfolios for {$month}");

        $successCount = 0;
        $errorCount = 0;

        foreach ($portfolios as $portfolio) {
            try {
                $this->info("Processing {$portfolio->name}...");
                
                // Run job synchronously for immediate feedback
                $job = new MakeMonthlySnapshotJob($portfolio, $monthDate);
                $job->handle();
                
                // Verify snapshot was created
                $snapshot = MonthlySnapshot::where('portfolio_id', $portfolio->id)
                    ->where('month', $month)
                    ->first();
                
                if ($snapshot) {
                    $this->info("✅ {$portfolio->name}: €" . number_format($snapshot->value, 2));
                    $this->info("   MoM: " . number_format($snapshot->mom_return * 100, 2) . "%");
                    $this->info("   YTD: " . number_format($snapshot->ytd_return * 100, 2) . "%");
                    $successCount++;
                } else {
                    $this->warn("⚠️  No snapshot created for {$portfolio->name}");
                    $errorCount++;
                }
                
            } catch (\Exception $e) {
                $this->error("❌ {$portfolio->name}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("📊 Snapshot Summary:");
        $this->info("- Success: {$successCount}");
        $this->info("- Errors: {$errorCount}");
        $this->info("- Month: {$month}");

        if ($successCount > 0) {
            $totalSnapshots = MonthlySnapshot::count();
            $this->info("- Total snapshots: {$totalSnapshots}");
        }

        return $errorCount > 0 ? 1 : 0;
    }
}
