<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Portfolio;
use App\Models\Position;
use UnitEnum;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static UnitEnum|string|null $navigationGroup = 'Portfolio Management';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('portfolio_id')
                            ->label('Portfolio')
                            ->options(Portfolio::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('position_id')
                            ->label('Position')
                            ->options(Position::with('portfolio')->get()->mapWithKeys(function ($position) {
                                $portfolio = $position->portfolio;
                                return [$position->id => "{$portfolio->name} - {$position->symbol}"];
                            }))
                            ->searchable(),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'BUY' => 'Buy',
                                'SELL' => 'Sell', 
                                'DIVIDEND' => 'Dividend',
                                'TAX' => 'Tax',
                                'FEE' => 'Fee',
                                'INTEREST' => 'Interest',
                                'CASH_IN' => 'Cash in (Deposit)',
                                'CASH_OUT' => 'Cash out (Withdrawal)',
                                'DEPOSIT' => 'Deposit (legacy)',
                                'WITHDRAWAL' => 'Withdrawal (legacy)',
                                'OTHER' => 'Other',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('symbol')
                            ->label('Symbol')
                            ->maxLength(10),
                        
                        Forms\Components\TextInput::make('isin')
                            ->label('ISIN')
                            ->maxLength(12),
                    ])->columns(2),
                
                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->step('0.00000001')
                            ->default(0),
                        
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('€')
                            ->default(0),
                        
                        Forms\Components\TextInput::make('currency')
                            ->default('EUR')
                            ->maxLength(3),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('€'),
                        
                        Forms\Components\TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->step('0.000001')
                            ->default(1),
                        
                        Forms\Components\TextInput::make('fees')
                            ->numeric()
                            ->step('0.01')
                            ->prefix('€')
                            ->default(0),
                    ])->columns(3),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TextInput::make('venue')
                            ->maxLength(50),
                        
                        Forms\Components\Textarea::make('description')
                            ->rows(2),
                        
                        Forms\Components\DateTimePicker::make('executed_at')
                            ->label('Execution Date')
                            ->required(),
                        
                        Forms\Components\TextInput::make('external_id')
                            ->label('External ID')
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                    ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('executed_at')
                    ->label('Date')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('portfolio.name')
                    ->label('Portfolio')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => ['BUY', 'DIVIDEND', 'INTEREST', 'CASH_IN', 'DEPOSIT'],
                        'danger' => ['SELL', 'TAX', 'FEE', 'CASH_OUT', 'WITHDRAWAL'],
                        'warning' => ['OTHER'],
                    ]),
                
                Tables\Columns\TextColumn::make('symbol')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric(decimalPlaces: 4)
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR', divideBy: 1)
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('EUR', divideBy: 1)
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('venue')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('portfolio_id')
                    ->label('Portfolio')
                    ->options(Portfolio::pluck('name', 'id')),
                
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'BUY' => 'Buy',
                        'SELL' => 'Sell',
                        'DIVIDEND' => 'Dividend',
                        'TAX' => 'Tax',
                        'FEE' => 'Fee',
                        'INTEREST' => 'Interest',
                        'CASH_IN' => 'Cash in',
                        'CASH_OUT' => 'Cash out',
                        'DEPOSIT' => 'Deposit (legacy)',
                        'WITHDRAWAL' => 'Withdrawal (legacy)',
                        'OTHER' => 'Other',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('executed_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($query) => $query->whereDate('executed_at', '>=', $data['from']))
                            ->when($data['until'], fn($query) => $query->whereDate('executed_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('executed_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
