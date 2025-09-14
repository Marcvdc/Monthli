<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioResource\Pages;
use App\Jobs\ImportStartingBalanceJob;
use App\Jobs\MakeMonthlySnapshotJob;
use App\Models\Portfolio;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;

class PortfolioResource extends Resource
{
    protected static ?string $model = Portfolio::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->helperText('Give your portfolio a descriptive name'),
            Forms\Components\TextInput::make('snapshot_day')
                ->numeric()
                ->minValue(1)
                ->maxValue(31)
                ->required()
                ->default(1)
                ->helperText('Day of the month to take monthly snapshots'),
            Forms\Components\TextInput::make('base_currency')
                ->required()
                ->maxLength(3)
                ->default('EUR')
                ->helperText('Base currency for portfolio calculations'),
            Forms\Components\DatePicker::make('balance_date')
                ->label('Starting Balance Date')
                ->helperText('Date when starting balance positions were recorded. Leave empty if importing transactions from the beginning.')
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('snapshot_day'),
                Tables\Columns\TextColumn::make('base_currency'),
                Tables\Columns\TextColumn::make('balance_date')->date()->label('Balance Date'),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Create Portfolio')
                    ->icon('heroicon-o-plus')
                    ->url(static::getUrl('create'))
                    ->color('primary'),
            ])
            ->actions([
                Action::make('importPortfolio')
                    ->label('Import Portfolio CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('DEGIRO Portfolio CSV')
                            ->acceptedFileTypes(['text/csv', 'application/csv'])
                            ->required()
                            ->helperText('Export your portfolio from DEGIRO (Portfolio → Export → CSV). Expected format: Product,Symbool/ISIN,Aantal,Slotkoers,Lokale waarde,,Waarde in EUR'),
                        
                        Forms\Components\DatePicker::make('balance_date')
                            ->label('Balance Date')
                            ->required()
                            ->helperText('Date when these positions were recorded')
                            ->default(today()),
                    ])
                    ->action(function (array $data, Portfolio $record) {
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
                        
                        ImportStartingBalanceJob::dispatch(
                            $tempPath,
                            $record->id,
                            $data['balance_date']
                        );
                        
                        Notification::make()
                            ->title('Portfolio import started')
                            ->body('Your portfolio positions are being imported. Refresh the page in a moment to see them.')
                            ->success()
                            ->send();
                    }),
                Action::make('view')
                    ->label('View Details')
                    ->url(fn (Portfolio $record): string => static::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
                EditAction::make(),
                Action::make('forceSnapshot')
                    ->label('Snapshot')
                    ->icon('heroicon-o-camera')
                    ->action(function (Portfolio $record) {
                        MakeMonthlySnapshotJob::dispatch($record);
                        Notification::make()
                            ->title('Snapshot queued')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolios::route('/'),
            'create' => Pages\CreatePortfolio::route('/create'),
            'view' => Pages\ViewPortfolio::route('/{record}'),
            'edit' => Pages\EditPortfolio::route('/{record}/edit'),
        ];
    }
}
