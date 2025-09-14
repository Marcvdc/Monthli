<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartingBalanceImportService
{
    /**
     * Expected DEGIRO portfolio CSV columns
     * Format: Product,Symbool/ISIN,Aantal,Slotkoers,Lokale waarde,,Waarde in EUR
     */
    const EXPECTED_COLUMNS = [
        'Product',
        'Symbool/ISIN', 
        'Aantal',
        'Slotkoers',
        'Lokale waarde',
        '', // Empty column
        'Waarde in EUR'
    ];

    /**
     * Import starting balance CSV and set portfolio balance date
     * 
     * @return array{success: int, errors: array<string>, positions_created: int}
     */
    public function importStartingBalance(string $filePath, Portfolio $portfolio, Carbon $balanceDate): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
            'positions_created' => 0,
        ];

        if (!file_exists($filePath)) {
            $results['errors'][] = 'File not found: ' . $filePath;
            return $results;
        }

        $file = fopen($filePath, 'r');
        if ($file === false) {
            $results['errors'][] = 'Cannot open file: ' . $filePath;
            return $results;
        }

        $headers = fgetcsv($file);
        if ($headers === false) {
            $results['errors'][] = 'Cannot read CSV headers';
            fclose($file);
            return $results;
        }

        // Validate CSV headers
        if (!$this->validateHeaders($headers)) {
            $results['errors'][] = 'Invalid CSV format. Expected columns: ' . implode(', ', self::EXPECTED_COLUMNS);
            fclose($file);
            return $results;
        }

        DB::beginTransaction();
        
        try {
            // Clear existing positions for this portfolio (fresh start)
            Position::where('portfolio_id', $portfolio->id)->delete();
            
            $rowNumber = 1;
            while (($row = fgetcsv($file)) !== false && $row !== null) {
                $rowNumber++;
                
                try {
                    $positionData = $this->parseRow($headers, $row, $portfolio);
                    
                    if ($positionData) {
                        Position::create($positionData);
                        $results['positions_created']++;
                    }
                    
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error('Starting balance import error', [
                        'row' => $rowNumber,
                        'data' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Set balance date on portfolio
            $portfolio->update(['balance_date' => $balanceDate]);

            DB::commit();
            
            Log::info('Starting balance import completed', [
                'portfolio_id' => $portfolio->id,
                'balance_date' => $balanceDate->format('Y-m-d'),
                'positions_created' => $results['positions_created'],
                'success' => $results['success'],
                'errors_count' => count($results['errors'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            $results['errors'][] = 'Transaction failed: ' . $e->getMessage();
            Log::error('Starting balance import failed', [
                'portfolio_id' => $portfolio->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        fclose($file);
        return $results;
    }

    /**
     * @param array<string> $headers
     */
    private function validateHeaders(array $headers): bool
    {
        $requiredColumns = ['Product', 'Symbool/ISIN', 'Aantal', 'Slotkoers'];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headers)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array<string> $headers
     * @param array<string> $row
     * @return array<string, mixed>|null
     */
    private function parseRow(array $headers, array $row, Portfolio $portfolio): ?array
    {
        $data = array_combine($headers, $row);
        
        // Skip empty or invalid rows
        if (empty($data['Product']) && empty($data['Symbool/ISIN'])) {
            return null;
        }

        $quantity = $this->parseQuantity($data['Aantal']);
        $price = $this->parsePrice($data['Slotkoers']);
        
        // Skip positions with zero quantity
        if ($quantity == 0) {
            return null;
        }

        // Extract symbol and ISIN from "Symbool/ISIN" column
        $symbolIsin = $data['Symbool/ISIN'] ?? '';
        $symbol = null;
        $isin = null;
        
        if ($symbolIsin) {
            // Check if it looks like an ISIN (12 characters, alphanumeric)
            if (preg_match('/^[A-Z]{2}[A-Z0-9]{10}$/', $symbolIsin)) {
                $isin = $symbolIsin;
            } else {
                $symbol = $symbolIsin;
            }
        }

        return [
            'portfolio_id' => $portfolio->id,
            'symbol' => $symbol,
            'isin' => $isin,
            'name' => $data['Product'] ?: null,
            'quantity' => $quantity,
            'average_price' => $price,
        ];
    }

    private function parseQuantity(string $quantity): float
    {
        if (empty($quantity)) {
            return 0;
        }
        
        // Handle European number format (comma as decimal separator)
        $quantity = str_replace([',', ' '], ['.', ''], $quantity);
        return (float) $quantity;
    }

    private function parsePrice(string $price): float
    {
        if (empty($price)) {
            return 0;
        }
        
        // Handle European number format and remove currency symbols
        $price = preg_replace('/[^\d.,-]/', '', $price);
        $price = str_replace(',', '.', $price);
        return (float) $price;
    }
}
