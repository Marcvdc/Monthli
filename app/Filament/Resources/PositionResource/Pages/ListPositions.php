<?php

namespace App\Filament\Resources\PositionResource\Pages;

use App\Filament\Resources\PositionResource;
use App\Models\Position;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListPositions extends ListRecords
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('portfolios')
                ->label('Import via Portfolio CSV')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url('/admin/portfolios')
                ->color('success')
                ->tooltip('Import positions using DEGIRO Portfolio CSV format'),
        ];
    }
}
