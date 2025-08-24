<?php

namespace App\Console\Commands;

use App\Jobs\MakeMonthlySnapshotJob;
use App\Models\Portfolio;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SnapshotsBackfill extends Command
{
    protected $signature = 'snapshots:backfill {from} {to} {--portfolio=}';

    protected $description = 'Backfill snapshot data';

    public function handle(): int
    {
        $fromArg = $this->argument('from');
        $toArg = $this->argument('to');
        
        $from = Carbon::createFromFormat('Y-m', (string) $fromArg);
        $to = Carbon::createFromFormat('Y-m', (string) $toArg);
        
        if (!$from || !$to) {
            $this->error('Invalid date format. Use Y-m format (e.g., 2024-01)');
            return self::FAILURE;
        }
        
        $from = $from->startOfMonth();
        $to = $to->startOfMonth();

        $portfolioId = $this->option('portfolio');
        $portfolios = $portfolioId
            ? Portfolio::where('id', $portfolioId)->get()
            : Portfolio::all();

        for ($date = $from->copy(); $date <= $to; $date->addMonth()) {
            foreach ($portfolios as $portfolio) {
                (new MakeMonthlySnapshotJob($portfolio, $date->copy()))->handle();
            }
        }

        return self::SUCCESS;
    }
}
