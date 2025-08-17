<?php

namespace App\Providers;

use App\Models\Dividend;
use App\Models\FxTick;
use App\Models\MonthlySnapshot;
use App\Models\Portfolio;
use App\Models\Position;
use App\Models\PriceTick;
use App\Models\Transaction;
use App\Policies\DividendPolicy;
use App\Policies\FxTickPolicy;
use App\Policies\MonthlySnapshotPolicy;
use App\Policies\PortfolioPolicy;
use App\Policies\PositionPolicy;
use App\Policies\PriceTickPolicy;
use App\Policies\TransactionPolicy;
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
