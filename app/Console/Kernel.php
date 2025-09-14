<?php

namespace App\Console;

use App\Jobs\IngestCryptoPricesJob;
use App\Jobs\IngestEquityPricesJob;
use App\Jobs\IngestFxRatesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Daily price ingestion at 6:00 AM (after markets close)
        $schedule->command('prices:ingest')->dailyAt('06:00');
        $schedule->job(new IngestCryptoPricesJob([]))->dailyAt('06:00');
        $schedule->job(new IngestFxRatesJob([]))->dailyAt('06:00');
        
        // Monthly snapshots on the last day of each month at 7:00 AM
        // (after price ingestion completes)
        $schedule->command('snapshot:make --all')->monthlyOn(-1, '07:00');
        
        // Daily snapshots for current month (useful for tracking)
        // Run at 7:30 AM after prices are updated
        $schedule->command('snapshot:make')->dailyAt('07:30');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
