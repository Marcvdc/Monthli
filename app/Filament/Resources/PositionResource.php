<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Position;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-pie';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('portfolio_id')
                ->relationship('portfolio', 'name')
                ->required(),
            Forms\Components\TextInput::make('symbol')
                ->required()
                ->maxLength(50),
            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('average_price')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('portfolio.name')->label('Portfolio')->sortable(),
                Tables\Columns\TextColumn::make('symbol')->searchable(),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('average_price'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
