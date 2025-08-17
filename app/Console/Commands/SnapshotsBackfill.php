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
        $from = Carbon::createFromFormat('Y-m', $this->argument('from'))->startOfMonth();
        $to = Carbon::createFromFormat('Y-m', $this->argument('to'))->startOfMonth();

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
