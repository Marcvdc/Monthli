<?php

namespace App\Filament\Widgets;

use App\Models\MonthlySnapshot;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;

class MonthlyReturnChart extends BarChartWidget
{
    protected ?string $heading = 'MoM rendement';

    protected function getData(): array
    {
        $snapshots = MonthlySnapshot::orderBy('month')->get();
        $data = [];
        $labels = [];
        $previous = null;

        foreach ($snapshots as $snapshot) {
            if ($previous) {
                $return = $previous->value != 0 ? (($snapshot->value - $previous->value) / $previous->value) * 100 : 0;
                $data[] = round($return, 2);
                $labels[] = Carbon::parse($snapshot->month)->format('Y-m');
            }
            $previous = $snapshot;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rendement %',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
