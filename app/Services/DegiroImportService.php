<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DegiroImportService
{
    /**
     * Expected DEGIRO CSV columns (actual export format)
     */
    const EXPECTED_COLUMNS = [
        'Datum',                    // Date
        'Tijd',                     // Time
        'Product',                  // Product name
        'ISIN',                     // ISIN code
        'Beurs',                    // Exchange
        'Uitvoeringsplaa',          // Execution place
        'Aantal',                   // Quantity
        'Koers',                    // Price
        '',                         // Empty column
        'Lokale waarde',            // Local value
        '',                         // Empty column
        'Waarde',                   // Value
        '',                         // Empty column
        'Wisselkoers',              // Exchange rate
        'Transactiekosten en/of',   // Transaction costs
        '',                         // Empty column
        'Totaal',                   // Total
        '',                         // Empty column
        'Order ID',                 // Order ID
    ];

    /**
     * Transaction type determination based on data patterns
     */
    const TRANSACTION_TYPES = [
        'BUY' => 'BUY',
        'SELL' => 'SELL',
        'DIVIDEND' => 'DIVIDEND',
        'TAX' => 'TAX',
        'FEE' => 'FEE',
        'CASH_IN' => 'CASH_IN',
        'CASH_OUT' => 'CASH_OUT',
        // Legacy aliases (handmatig ingevoerde transacties)
        'DEPOSIT' => 'DEPOSIT',
        'WITHDRAWAL' => 'WITHDRAWAL',
        'OTHER' => 'OTHER',
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

        // Batch and file metadata for this import run
        $importBatchId = (string) Str::uuid();
        $sourceFileName = basename($filePath);
        $sourceFileHash = hash_file('sha256', $filePath) ?: null;

        $rowNumber = 1;
        while (($row = fgetcsv($file)) !== false && $row !== null) {
            $rowNumber++;
            
            try {
                $transactionData = $this->parseRow($headers, $row, $portfolio);
                
                if ($transactionData) {
                    // Attach batch and file metadata
                    $transactionData['import_batch_id'] = $importBatchId;
                    $transactionData['source_file_name'] = $sourceFileName;
                    $transactionData['source_file_hash'] = $sourceFileHash;
                    // Check for duplicates
                    if ($this->isDuplicate($transactionData)) {
                        $results['duplicates']++;
                        continue;
                    }
                    
                    $transaction = Transaction::create($transactionData);
            
                    // Update position after creating transaction
                    // Only update positions for transactions after balance_date to avoid double counting
                    if ($transaction->position_id && in_array($transaction->type, ['BUY', 'SELL']) && $this->shouldUpdatePosition($transaction, $portfolio)) {
                        $this->updatePositionFromTransaction($transaction);
                    }
                    
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
        if (empty($data['Datum']) || empty($data['Product'])) {
            return null;
        }

        // Parse date and time
        $dateTime = $this->parseDateTime($data['Datum'], $data['Tijd'] ?? '00:00');
        
        // Determine transaction type based on quantity and values
        $type = $this->parseTransactionType($data);
        
        // Extract numeric values from columns
        $quantity = $this->parseQuantity($data['Aantal']);
        $price = $this->parsePrice($data['Koers']);
        $totalAmount = $this->parseAmount($data['Totaal']);
        $exchangeRate = $this->parseExchangeRate($data['Wisselkoers']);
        $fees = $this->parseFees($data['Transactiekosten en/of']);

        // Generate external ID from order ID or row hash
        $orderId = $data['Order ID'] ?? null;
        $externalId = $orderId ?: 'degiro_' . md5(json_encode($data) ?: '');

        return [
            'portfolio_id' => $portfolio->id,
            'position_id' => $this->findOrCreatePosition($portfolio, $data),
            'type' => $type,
            'symbol' => $this->extractSymbol($data['Product']),
            'isin' => $data['ISIN'] ?? null,
            'quantity' => $quantity,
            'price' => $price,
            'currency' => $this->extractCurrency($data),
            'total_amount' => $totalAmount,
            'exchange_rate' => $exchangeRate,
            'fees' => $fees,
            'venue' => 'DEGIRO',
            'description' => $this->buildDescription($data),
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

    private function parseTransactionType(array $data): string
    {
        $quantity = (float) str_replace(',', '.', $data['Aantal'] ?: '0');
        $total = (float) str_replace(',', '.', $data['Totaal'] ?: '0');
        $product = $data['Product'] ?? '';
        
        // Determine type based on data patterns (see PLAN T1 mapping)
        if ($quantity > 0 && $total < 0) {
            // BUY: Product != 'EUR', Aantal > 0, Totaal < 0
            return 'BUY';
        }

        if ($quantity < 0 && $total > 0) {
            // SELL: Product != 'EUR', Aantal < 0, Totaal > 0
            return 'SELL';
        }

        if ($quantity == 0 && $total > 0) {
            // DIVIDEND vs CASH_IN (storting)
            if (!empty($product) && $product !== 'EUR') {
                return 'DIVIDEND';
            }

            return 'CASH_IN';
        }

        if ($quantity == 0 && $total < 0) {
            // CASH_OUT (opname) of FEE
            if ($product === 'EUR') {
                return 'CASH_OUT';
            }

            return 'FEE';
        }
        
        return 'OTHER';
    }

    private function parseQuantity(string $aantal): float
    {
        if (empty($aantal)) {
            return 0;
        }
        
        // Parse quantity directly from Aantal column
        return (float) str_replace(',', '.', $aantal);
    }

    private function parsePrice(string $koers): float
    {
        if (empty($koers)) {
            return 0;
        }
        
        // Parse price directly from Koers column
        return (float) str_replace(',', '.', $koers);
    }

    private function parseAmount(string $totaal): float
    {
        if (empty($totaal)) {
            return 0;
        }
        
        // Parse total amount from Totaal column
        $cleanAmount = preg_replace('/[^\d.,-]/', '', $totaal);
        return (float) str_replace(',', '.', $cleanAmount ?? '');
    }

    private function parseExchangeRate(string $wisselkoers): float
    {
        if (empty($wisselkoers) || $wisselkoers === '-') {
            return 1.0;
        }
        
        return (float) str_replace(',', '.', $wisselkoers);
    }

    private function extractSymbol(string $product): ?string
    {
        // Extract symbol from product name (e.g., "Apple Inc. (AAPL)" -> "AAPL")
        if (preg_match('/\(([A-Z]{2,5})\)/', $product, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function extractCurrency(array $data): string
    {
        // Try to extract currency from various fields
        $waarde = $data['Waarde'] ?? '';
        $lokaleWaarde = $data['Lokale waarde'] ?? '';
        $totaal = $data['Totaal'] ?? '';
        
        $currencies = ['EUR', 'USD', 'GBP', 'CHF', 'CAD', 'AUD', 'JPY'];
        
        foreach ([$waarde, $lokaleWaarde, $totaal] as $field) {
            foreach ($currencies as $currency) {
                if (stripos($field, $currency) !== false) {
                    return $currency;
                }
            }
        }
        
        return 'EUR'; // Default to EUR
    }

    private function parseFees(string $transactiekosten): float
    {
        if (empty($transactiekosten)) {
            return 0;
        }
        
        // Parse fees from Transactiekosten column
        $cleanAmount = preg_replace('/[^\d.,-]/', '', $transactiekosten);
        return abs((float) str_replace(',', '.', $cleanAmount ?? ''));
    }
    
    private function buildDescription(array $data): string
    {
        $product = $data['Product'] ?? '';
        $beurs = $data['Beurs'] ?? '';
        $aantal = $data['Aantal'] ?? '';
        $koers = $data['Koers'] ?? '';
        
        $parts = [];
        if ($product) $parts[] = $product;
        if ($beurs) $parts[] = "on {$beurs}";
        if ($aantal && $koers) $parts[] = "{$aantal} @ {$koers}";
        
        return implode(' | ', $parts);
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
        // Check by external_id first (Order ID from DEGIRO)
        if (!empty($transactionData['external_id']) && !str_starts_with($transactionData['external_id'], 'degiro_')) {
            return Transaction::where('external_id', $transactionData['external_id'])
                ->where('portfolio_id', $transactionData['portfolio_id'])
                ->exists();
        }

        // For transactions without Order ID, check by unique combination
        // of date, symbol, ISIN, quantity, price to detect overlapping uploads
        return Transaction::where('portfolio_id', $transactionData['portfolio_id'])
            ->where('executed_at', $transactionData['executed_at'])
            ->where(function($query) use ($transactionData) {
                if ($transactionData['symbol']) {
                    $query->where('symbol', $transactionData['symbol']);
                }
                if ($transactionData['isin']) {
                    $query->orWhere('isin', $transactionData['isin']);
                }
            })
            ->where('type', $transactionData['type'])
            ->where('quantity', $transactionData['quantity'])
            ->where('price', $transactionData['price'])
            ->exists();
    }

    /**
     * Check if position should be updated for this transaction
     * Only update if transaction is after portfolio's balance_date
     */
    private function shouldUpdatePosition(Transaction $transaction, Portfolio $portfolio): bool
    {
        // If no balance_date is set, always update positions
        if (!$portfolio->balance_date) {
            return true;
        }

        // Only update positions for transactions after the balance date
        return $transaction->executed_at->greaterThan($portfolio->balance_date);
    }

    /**
     * Update position quantity and average price after transaction
     */
    private function updatePositionFromTransaction(Transaction $transaction): void
    {
        $position = Position::find($transaction->position_id);
        if (!$position) {
            return;
        }

        $currentQuantity = $position->quantity;
        $currentPrice = $position->average_price;
        $transactionQuantity = $transaction->quantity;
        $transactionPrice = $transaction->price;

        if ($transaction->type === 'BUY') {
            // Calculate new average price for buy transactions
            $totalCost = ($currentQuantity * $currentPrice) + ($transactionQuantity * $transactionPrice);
            $newQuantity = $currentQuantity + $transactionQuantity;
            $newAveragePrice = $newQuantity > 0 ? $totalCost / $newQuantity : 0;

            $position->update([
                'quantity' => $newQuantity,
                'average_price' => $newAveragePrice,
            ]);
        } elseif ($transaction->type === 'SELL') {
            // For sell transactions, reduce quantity but keep average price
            $newQuantity = $currentQuantity + $transactionQuantity; // quantity is negative for sells
            
            $position->update([
                'quantity' => $newQuantity,
                // Keep the same average_price for sells
            ]);
        }
    }
}
