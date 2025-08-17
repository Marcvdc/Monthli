<?php

namespace App\Filament\Resources\PositionResource\Pages;

use App\Filament\Resources\PositionResource;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use App\Models\Position;

class ListPositions extends ListRecords
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import')
                ->form([
                    Forms\Components\FileUpload::make('file')->required(),
                ])
                ->action(function (array $data): void {
                    $path = $data['file'];
                    $rows = array_map('str_getcsv', file($path));
                    foreach ($rows as $row) {
                        if (count($row) < 4) {
                            continue;
                        }
                        Position::create([
                            'portfolio_id' => $row[0],
                            'symbol' => $row[1],
                            'quantity' => $row[2],
                            'average_price' => $row[3],
                        ]);
                    }
                }),
        ];
    }
}
