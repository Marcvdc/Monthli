<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioResource\Pages;
use App\Jobs\MakeMonthlySnapshotJob;
use App\Models\Portfolio;
use BackedEnum;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioResource extends Resource
{
    protected static ?string $model = Portfolio::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('snapshot_day')
                ->numeric()
                ->minValue(1)
                ->maxValue(31)
                ->required(),
            Forms\Components\TextInput::make('base_currency')
                ->required()
                ->maxLength(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('snapshot_day'),
                Tables\Columns\TextColumn::make('base_currency'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('forceSnapshot')
                    ->label('Force Snapshot')
                    ->action(function (Portfolio $record) {
                        MakeMonthlySnapshotJob::dispatch($record);
                        Notification::make()
                            ->title('Snapshot queued')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('backfill')
                    ->label('Backfill')
                    ->action(function (Portfolio $record) {
                        MakeMonthlySnapshotJob::dispatch($record);
                        Notification::make()
                            ->title('Backfill queued')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolios::route('/'),
            'create' => Pages\CreatePortfolio::route('/create'),
            'edit' => Pages\EditPortfolio::route('/{record}/edit'),
        ];
    }
}
