<?php

namespace App\Filament\Resources\PortfolioResource\Pages;

use App\Filament\Resources\PortfolioResource;
use App\Jobs\ImportDegiroCsvJob;
use App\Jobs\ImportStartingBalanceJob;
use App\Models\Portfolio;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePortfolio extends CreateRecord
{
    protected static string $resource = PortfolioResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the portfolio view page after creation
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Create Portfolio')
            ->after(function (Portfolio $record) {
                // Show success notification with next steps
                Notification::make()
                    ->title('Portfolio created successfully!')
                    ->body('You can now import your starting balance and transaction history.')
                    ->success()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('import_data')
                            ->button()
                            ->label('Import Data')
                            ->url($this->getResource()::getUrl('view', ['record' => $record])),
                    ])
                    ->send();
            });
    }
}
