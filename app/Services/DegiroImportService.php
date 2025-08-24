<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DegiroImportService
{
    /**
     * Expected DEGIRO CSV columns
     */
    const EXPECTED_COLUMNS = [
        'Datum',           // Date
        'Tijd',            // Time
        'Product',         // Product name
        'ISIN',           // ISIN code
        'Beschrijving',    // Description
        'FX',             // Exchange rate
        'Mutatie',        // Change/Transaction type
        'Saldo',          // Balance
        'Order Id',       // Order ID
    ];

    /**
     * Transaction type mapping from Dutch to English
     */
    const TRANSACTION_TYPES = [
        'Koop' => 'BUY',
        'Verkoop' => 'SELL',
        'Dividend' => 'DIVIDEND',
        'Belasting' => 'TAX',
        'Kosten' => 'FEE',
        'Rente' => 'INTEREST',
        'Valutawissel' => 'CURRENCY_EXCHANGE',
        'Storting' => 'DEPOSIT',
        'Opname' => 'WITHDRAWAL',
    ];

    /**
     * @return array{success: int, errors: array<string>, duplicates: int}
     */
    public function importCsv(string $filePath, Portfolio $portfolio): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
            'duplicates' => 0,
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
            $results['errors'][] = 'Invalid DEGIRO CSV format. Expected columns: ' . implode(', ', self::EXPECTED_COLUMNS);
            fclose($file);
            return $results;
        }

        $rowNumber = 1;
        while (($row = fgetcsv($file)) !== false && $row !== null) {
            $rowNumber++;
            
            try {
                $transactionData = $this->parseRow($headers, $row, $portfolio);
                
                if ($transactionData) {
                    // Check for duplicates
                    if ($this->isDuplicate($transactionData)) {
                        $results['duplicates']++;
                        continue;
                    }
                    
                    Transaction::create($transactionData);
                    $results['success']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                Log::error('DEGIRO import error', [
                    'row' => $rowNumber,
                    'data' => $row,
                    'error' => $e->getMessage()
                ]);
            }
        }

        fclose($file);
        return $results;
    }

    /**
     * @param array<string> $headers
     */
    private function validateHeaders(array $headers): bool
    {
        foreach (self::EXPECTED_COLUMNS as $column) {
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
        if (empty($data['Datum']) || empty($data['Beschrijving'])) {
            return null;
        }

        // Parse date and time
        $dateTime = $this->parseDateTime($data['Datum'], $data['Tijd'] ?? '00:00');
        
        // Determine transaction type
        $type = $this->parseTransactionType($data['Beschrijving']);
        
        // Extract numeric values
        $quantity = $this->parseQuantity($data['Beschrijving']);
        $price = $this->parsePrice($data['Beschrijving']);
        $totalAmount = $this->parseAmount($data['Saldo']);
        $exchangeRate = $this->parseExchangeRate($data['FX']);

        // Generate external ID from order ID or row hash
        $orderId = $data['Order Id'] ?? null;
        $externalId = $orderId ?: 'degiro_' . md5(json_encode($data) ?: '');

        return [
            'portfolio_id' => $portfolio->id,
            'position_id' => $this->findOrCreatePosition($portfolio, $data),
            'type' => $type,
            'symbol' => $this->extractSymbol($data['Product']),
            'isin' => $data['ISIN'] ?? null,
            'quantity' => $quantity,
            'price' => $price,
            'currency' => $this->extractCurrency($data['Beschrijving']),
            'total_amount' => $totalAmount,
            'exchange_rate' => $exchangeRate,
            'fees' => $this->extractFees($data['Beschrijving']),
            'venue' => 'DEGIRO',
            'description' => $data['Beschrijving'],
            'executed_at' => $dateTime,
            'external_id' => $externalId,
            'raw_data' => $data,
        ];
    }

    private function parseDateTime(string $date, string $time): Carbon
    {
        // DEGIRO date format: dd-mm-yyyy
        // DEGIRO time format: HH:mm
        $dateTime = Carbon::createFromFormat('d-m-Y H:i', $date . ' ' . $time);
        if (!$dateTime) {
            throw new \InvalidArgumentException('Invalid date format: ' . $date . ' ' . $time);
        }
        return $dateTime;
    }

    private function parseTransactionType(string $description): string
    {
        foreach (self::TRANSACTION_TYPES as $dutch => $english) {
            if (stripos($description, $dutch) !== false) {
                return $english;
            }
        }
        
        // Default to OTHER if no type matched
        return 'OTHER';
    }

    private function parseQuantity(string $description): float
    {
        // Extract quantity from description (e.g., "Koop 10 AAPL")
        if (preg_match('/(\d+(?:[.,]\d+)?)(?:\s+stuks?)?/i', $description, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }
        
        return 0;
    }

    private function parsePrice(string $description): float
    {
        // Extract price from description (e.g., "@ 150.25 EUR")
        if (preg_match('/@\s*([0-9,]+\.?\d*)/i', $description, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }
        
        return 0;
    }

    private function parseAmount(string $balance): float
    {
        // Parse balance amount (e.g., "1,250.50 EUR" or "-500.00")
        $cleanAmount = preg_replace('/[^\d.,-]/', '', $balance);
        return (float) str_replace(',', '', $cleanAmount ?? '');
    }

    private function parseExchangeRate(string $fx): float
    {
        if (empty($fx) || $fx === '-') {
            return 1.0;
        }
        
        return (float) str_replace(',', '.', $fx);
    }

    private function extractSymbol(string $product): ?string
    {
        // Extract symbol from product name (e.g., "Apple Inc. (AAPL)" -> "AAPL")
        if (preg_match('/\(([A-Z]{2,5})\)/', $product, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function extractCurrency(string $description): string
    {
        // Common currencies in DEGIRO
        $currencies = ['EUR', 'USD', 'GBP', 'CHF', 'CAD', 'AUD', 'JPY'];
        
        foreach ($currencies as $currency) {
            if (stripos($description, $currency) !== false) {
                return $currency;
            }
        }
        
        return 'EUR'; // Default to EUR
    }

    private function extractFees(string $description): float
    {
        // Look for transaction costs/fees
        if (preg_match('/kosten[:\s]*([0-9,]+\.?\d*)/i', $description, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }
        
        return 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function findOrCreatePosition(Portfolio $portfolio, array $data): ?int
    {
        $product = $data['Product'] ?? '';
        $symbol = $this->extractSymbol(is_string($product) ? $product : '');
        $isin = $data['ISIN'] ?? null;
        
        if (!$symbol && !$isin) {
            return null; // Can't create position without identifier
        }

        $query = Position::where('portfolio_id', $portfolio->id);
        $query->where(function ($q) use ($symbol, $isin) {
            if ($symbol) {
                $q->where('symbol', $symbol);
            }
            if ($isin) {
                $q->orWhere('isin', $isin);
            }
        });
        $position = $query->first();

        if (!$position && $symbol) {
            $position = Position::create([
                'portfolio_id' => $portfolio->id,
                'symbol' => $symbol,
                'isin' => $isin,
                'quantity' => 0,
                'average_price' => 0,
            ]);
        }

        return $position instanceof Position ? $position->id : null;
    }

    /**
     * @param array<string, mixed> $transactionData
     */
    private function isDuplicate(array $transactionData): bool
    {
        return Transaction::where('external_id', $transactionData['external_id'])
            ->where('portfolio_id', $transactionData['portfolio_id'])
            ->exists();
    }
}
