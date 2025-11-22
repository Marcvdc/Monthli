<?php

namespace App\Console\Commands;

use App\Services\StartingBalanceImportService;
use Illuminate\Console\Command;

class TestSymbolExtraction extends Command
{
    protected $signature = 'test:symbol-extraction';
    protected $description = 'Test symbol/ISIN extraction from Symbool/ISIN column';

    public function handle(): int
    {
        $this->info('🧪 Testing Symbol/ISIN Extraction Logic');

        // Sample data from DEGIRO CSV
        $testData = [
            ['Product' => 'ASML Holding NV', 'Symbool/ISIN' => 'NL0010273215'],
            ['Product' => 'Apple Inc', 'Symbool/ISIN' => 'AAPL'], 
            ['Product' => 'Microsoft Corporation', 'Symbool/ISIN' => 'MSFT'],
            ['Product' => 'Vanguard S&P 500 UCITS ETF', 'Symbool/ISIN' => 'IE00B3XXRP09'],
        ];

        foreach ($testData as $data) {
            $symbolIsin = $data['Symbool/ISIN'];
            $symbol = null;
            $isin = null;
            
            // Same logic as in StartingBalanceImportService
            if ($symbolIsin) {
                // Check if it looks like an ISIN (12 characters, alphanumeric)
                if (preg_match('/^[A-Z]{2}[A-Z0-9]{10}$/', $symbolIsin)) {
                    $isin = $symbolIsin;
                } else {
                    $symbol = $symbolIsin;
                }
            }

            $this->info("📊 {$data['Product']}:");
            $this->info("  Input: {$symbolIsin}");
            $this->info("  Symbol: " . ($symbol ?: 'null'));
            $this->info("  ISIN: " . ($isin ?: 'null'));
            $this->info("  ISIN Match: " . (preg_match('/^[A-Z]{2}[A-Z0-9]{10}$/', $symbolIsin) ? 'YES' : 'NO'));
            $this->newLine();
        }

        return 0;
    }
}
