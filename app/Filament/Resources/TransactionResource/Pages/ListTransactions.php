<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Jobs\ImportDegiroCsvJob;
use App\Models\Portfolio;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importCsv')
                ->label('Import DEGIRO CSV')
                ->icon('heroicon-o-document-arrow-up')
                ->form([
                    Forms\Components\Select::make('portfolio_id')
                        ->label('Portfolio')
                        ->options(Portfolio::pluck('name', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\FileUpload::make('csv_file')
                        ->label('DEGIRO CSV File')
                        ->acceptedFileTypes(['text/csv', 'application/csv'])
                        ->required()
                        ->directory('temp-csv')
                        ->visibility('private'),
                ])
                ->action(function (array $data): void {
                    $csvFile = $data['csv_file'];
                    $portfolio = Portfolio::find($data['portfolio_id']);

                    // Dispatch job to process CSV import
                    ImportDegiroCsvJob::dispatch($csvFile, $portfolio);

                    Notification::make()
                        ->title('CSV Import Started')
                        ->body('Your DEGIRO CSV is being processed. You will be notified when complete.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
