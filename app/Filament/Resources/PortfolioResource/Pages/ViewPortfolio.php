<?php

namespace App\Filament\Resources\PortfolioResource\Pages;

use App\Filament\Resources\PortfolioResource;
use App\Jobs\ImportDegiroCsvJob;
use App\Jobs\ImportStartingBalanceJob;
use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPortfolio extends ViewRecord
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Portfolios')
                ->icon('heroicon-o-arrow-left')
                ->url('/admin/portfolios')
                ->color('gray'),
        ];
    }

}
