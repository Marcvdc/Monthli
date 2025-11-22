<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Services\PortfolioReplayService;
use Illuminate\Console\Command;

class ReplayPortfolio extends Command
{
    protected $signature = 'portfolio:replay {portfolio_id}';

    protected $description = 'Rebuild positions for a portfolio from its transactions (FULL_HISTORY mode)';

    public function handle(PortfolioReplayService $service): int
    {
        $portfolioId = (int) $this->argument('portfolio_id');
        $portfolio = Portfolio::find($portfolioId);

        if (! $portfolio) {
            $this->error("Portfolio {$portfolioId} not found");

            return self::FAILURE;
        }

        $this->info("Replaying portfolio {$portfolio->id} – {$portfolio->name}");

        $service->replay($portfolio);

        $this->info('Replay completed. Positions are now derived from transactions.');

        return self::SUCCESS;
    }
}
