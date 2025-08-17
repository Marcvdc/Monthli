<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SnapshotsBackfill extends Command
{
    protected $signature = 'snapshots:backfill';
    protected $description = 'Backfill snapshot data';

    public function handle(): int
    {
        // TODO: implement backfill logic
        return self::SUCCESS;
    }
}
