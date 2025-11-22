<?php

namespace App\Console\Commands;

use App\Services\StartingBalanceImportService;
use Illuminate\Console\Command;

class DebugCsvParsing extends Command
{
    protected $signature = 'debug:csv-parsing {file}';
    protected $description = 'Debug CSV parsing for portfolio import';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('🔍 Debugging CSV Parsing');
        $this->info("File: {$filePath}");

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        
        $this->info("Headers: " . implode(' | ', $headers));
        $this->newLine();

        $rowNumber = 1;
        while (($row = fgetcsv($file)) !== false && $row !== null && $rowNumber <= 5) {
            $rowNumber++;
            $data = array_combine($headers, $row);
            
            $this->info("Row {$rowNumber}:");
            foreach ($data as $key => $value) {
                $this->info("  {$key}: '{$value}'");
            }
            
            // Test parsing logic
            $product = $data['Product'] ?? '';
            $symbolIsin = $data['Symbool/ISIN'] ?? '';
            $quantity = $data['Aantal'] ?? '';
            $price = $data['Slotkoers'] ?? '';
            
            $this->info("Parsed:");
            $this->info("  Product: '{$product}' (empty: " . (empty($product) ? 'YES' : 'NO') . ")");
            $this->info("  Symbool/ISIN: '{$symbolIsin}'");
            $this->info("  Quantity: '{$quantity}'");
            $this->info("  Price: '{$price}'");
            $this->newLine();
        }

        fclose($file);
        return 0;
    }
}
