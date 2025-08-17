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
        $schedule->job(new IngestEquityPricesJob([]))->dailyAt('06:00');
        $schedule->job(new IngestCryptoPricesJob([]))->dailyAt('06:00');
        $schedule->job(new IngestFxRatesJob([]))->dailyAt('06:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
