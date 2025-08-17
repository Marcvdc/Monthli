<?php

namespace App\Filament\Widgets;

use App\Models\MonthlySnapshot;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class MonthlyValueChart extends LineChartWidget
{
    protected ?string $heading = 'Waarde per maand';

    protected function getData(): array
    {
        $snapshots = MonthlySnapshot::orderBy('month')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Waarde',
                    'data' => $snapshots->pluck('value')->toArray(),
                ],
            ],
            'labels' => $snapshots->pluck('month')->map(fn ($m) => Carbon::parse($m)->format('Y-m'))->toArray(),
        ];
    }
}
