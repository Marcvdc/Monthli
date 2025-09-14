<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Jobs\ImportDegiroCsvJob;
use App\Models\Portfolio;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Http\UploadedFile;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('importTransactions')
                ->label('Import Transaction CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('portfolio_id')
                        ->label('Portfolio')
                        ->options(Portfolio::pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->helperText('Select the portfolio to import transactions into'),
                    
                    Forms\Components\FileUpload::make('csv_file')
                        ->label('DEGIRO Transaction CSV')
                        ->acceptedFileTypes(['text/csv', 'application/csv'])
                        ->required()
                        ->helperText('Export your account overview from DEGIRO (Account → Export → CSV). Expected format: Datum,Tijd,Product,ISIN,Beurs,Uitvoeringsplaa,Aantal,Koers,,Lokale waarde,,Waarde,,Wisselkoers,Transactiekosten en/of,,Totaal,,Order ID'),
                ])
                ->action(function (array $data) {
                    $uploadedFile = $data['csv_file'];
                    
                    // Handle file path from Filament upload
                    if (is_string($uploadedFile)) {
                        $sourcePath = \Illuminate\Support\Facades\Storage::path($uploadedFile);
                    } else {
                        $sourcePath = $uploadedFile->getRealPath();
                    }
                    
                    $tempPath = storage_path('app/temp/' . uniqid() . '.csv');
                    
                    if (!file_exists(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0755, true);
                    }
                    
                    copy($sourcePath, $tempPath);
                    
                    ImportDegiroCsvJob::dispatch(
                        $tempPath,
                        $data['portfolio_id']
                    );
                    
                    Notification::make()
                        ->title('Transaction import started')
                        ->body('Your transactions are being imported. Refresh the page in a moment to see them.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
