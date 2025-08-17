<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonthlySnapshotResource\Pages;
use App\Models\MonthlySnapshot;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ExportAction;

class MonthlySnapshotResource extends Resource
{
    protected static ?string $model = MonthlySnapshot::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('portfolio.name')->label('Portfolio'),
                Tables\Columns\TextColumn::make('month')->date('Y-m'),
                Tables\Columns\TextColumn::make('value'),
            ])
            ->headerActions([
                ExportAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonthlySnapshots::route('/'),
        ];
    }
}
