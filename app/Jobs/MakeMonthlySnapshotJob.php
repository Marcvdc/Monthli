<?php

namespace App\Jobs;

use App\Models\{FxTick, MonthlySnapshot, Portfolio, PriceTick};
use Carbon\Carbon;

class MakeMonthlySnapshotJob
{
    public function __construct(
        protected Portfolio $portfolio,
        protected Carbon $date
    ) {
    }

    public function handle(): void
    {
        $month = $this->date->copy()->startOfMonth();
        $monthEnd = $this->date->copy()->endOfMonth();

        $total = 0.0;
        foreach ($this->portfolio->positions as $position) {
            $price = PriceTick::where('symbol', $position->symbol)
                ->whereDate('date', '<=', $monthEnd)
                ->orderByDesc('date')
                ->first();

            if (! $price) {
                continue;
            }

            $value = (float) $position->quantity * (float) $price->price;

            if ($position->currency !== 'EUR') {
                $fx = FxTick::where('base_currency', $position->currency)
                    ->where('quote_currency', 'EUR')
                    ->whereDate('date', '<=', $monthEnd)
                    ->orderByDesc('date')
                    ->first();

                $rate = $fx?->rate ?? 1;
                $value *= (float) $rate;
            }

            $total += $value;
        }

        $previous = MonthlySnapshot::where('portfolio_id', $this->portfolio->id)
            ->where('month', '<', $month)
            ->orderByDesc('month')
            ->first();

        $mom = $previous && $previous->value != 0
            ? ($total - $previous->value) / $previous->value
            : null;

        $yearStartSnapshot = MonthlySnapshot::where('portfolio_id', $this->portfolio->id)
            ->whereBetween('month', [$month->copy()->startOfYear(), $month])
            ->orderBy('month')
            ->first();

        $ytd = $yearStartSnapshot && $yearStartSnapshot->value != 0
            ? ($total - $yearStartSnapshot->value) / $yearStartSnapshot->value
            : null;

        $maxValue = MonthlySnapshot::where('portfolio_id', $this->portfolio->id)
            ->where('month', '<=', $month)
            ->max('value');
        $maxValue = max($maxValue, $total);
        $drawdown = $maxValue != 0 ? ($total - $maxValue) / $maxValue : null;

        $values = MonthlySnapshot::where('portfolio_id', $this->portfolio->id)
            ->where('month', '<', $month)
            ->orderBy('month')
            ->pluck('value')
            ->toArray();
        $values[] = $total;

        $logReturns = [];
        for ($i = 1; $i < count($values); $i++) {
            $prev = $values[$i - 1];
            $curr = $values[$i];
            if ($prev > 0 && $curr > 0) {
                $logReturns[] = log($curr / $prev);
            }
        }

        if (count($logReturns) > 1) {
            $mean = array_sum($logReturns) / count($logReturns);
            $variance = array_sum(array_map(fn ($x) => pow($x - $mean, 2), $logReturns)) / (count($logReturns) - 1);
            $volatility = sqrt($variance);
        } else {
            $volatility = count($logReturns) === 1 ? 0.0 : null;
        }

        MonthlySnapshot::updateOrCreate(
            [
                'portfolio_id' => $this->portfolio->id,
                'month' => $month,
            ],
            [
                'value' => $total,
                'mom' => $mom,
                'ytd' => $ytd,
                'drawdown' => $drawdown,
                'volatility' => $volatility,
            ]
        );
    }
}
