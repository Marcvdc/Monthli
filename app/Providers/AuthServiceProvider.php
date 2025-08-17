<?php

namespace App\Providers;

use App\Models\{Dividend, FxTick, MonthlySnapshot, Portfolio, Position, PriceTick, Transaction};
use App\Policies\{DividendPolicy, FxTickPolicy, MonthlySnapshotPolicy, PortfolioPolicy, PositionPolicy, PriceTickPolicy, TransactionPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Portfolio::class => PortfolioPolicy::class,
        Position::class => PositionPolicy::class,
        Transaction::class => TransactionPolicy::class,
        Dividend::class => DividendPolicy::class,
        PriceTick::class => PriceTickPolicy::class,
        FxTick::class => FxTickPolicy::class,
        MonthlySnapshot::class => MonthlySnapshotPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
