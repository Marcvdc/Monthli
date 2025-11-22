<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DegiroImportService;
use Illuminate\Console\Command;

class DebugDegiroImport extends Command
{
    protected $signature = 'debug:degiro-import {--fresh}';
    protected $description = 'Debug DEGIRO CSV import functionality';

    public function handle(DegiroImportService $importService)
    {
        $this->info('🧪 Debugging DEGIRO CSV Import');
        $this->newLine();

        // Create test user and portfolio
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $portfolio = Portfolio::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Test Portfolio'],
            [
                'base_currency' => 'EUR',
                'snapshot_day' => 1,
            ]
        );

        $this->info("✅ User: {$user->email}");
        $this->info("✅ Portfolio: {$portfolio->name} (ID: {$portfolio->id})");
        $this->newLine();

        // Clear data if fresh
        if ($this->option('fresh')) {
            Transaction::where('portfolio_id', $portfolio->id)->delete();
            Position::where('portfolio_id', $portfolio->id)->delete();
            $this->info('🧹 Cleared existing data');
        }

        // Check CSV file
        $csvFile = base_path('tests/Fixtures/csv/degiro_sample.csv');
        if (!file_exists($csvFile)) {
            $this->error("❌ CSV not found: {$csvFile}");
            return 1;
        }

        $this->info("📁 CSV file: {$csvFile}");

        // Read and show CSV structure
        $lines = file($csvFile);
        $headers = str_getcsv($lines[0]);
        $this->info('📋 CSV Headers (' . count($headers) . ' columns):');
        foreach ($headers as $i => $header) {
            $this->info("   {$i}: '{$header}'");
        }

        // Show first data row
        if (isset($lines[1])) {
            $this->newLine();
            $this->info('📄 First data row:');
            $firstRow = str_getcsv($lines[1]);
            foreach ($firstRow as $i => $value) {
                $this->info("   {$headers[$i]}: '{$value}'");
            }
        }

        $this->newLine();

        // Test import
        $this->info('🔄 Testing import...');
        $results = $importService->importCsv($csvFile, $portfolio);

        $this->info("✅ Results: Success={$results['success']}, Duplicates={$results['duplicates']}, Errors=" . count($results['errors']));

        if (!empty($results['errors'])) {
            $this->newLine();
            $this->warn('⚠️ Errors:');
            foreach ($results['errors'] as $error) {
                $this->warn("   - {$error}");
            }
        }

        // Show imported transactions
        $this->newLine();
        $transactions = Transaction::where('portfolio_id', $portfolio->id)->get();
        $this->info("📊 Imported {$transactions->count()} transactions:");

        foreach ($transactions as $t) {
            $this->info(sprintf('   %s | %-8s | %-6s | %8s @ %8s = %10s %s',
                $t->executed_at->format('M-d'),
                $t->type,
                $t->symbol ?: 'N/A',
                number_format($t->quantity, 2),
                number_format($t->price, 2),
                number_format($t->total_amount, 2),
                $t->currency
            ));
        }

        // Show positions
        $positions = Position::where('portfolio_id', $portfolio->id)->get();
        if ($positions->count() > 0) {
            $this->newLine();
            $this->info("📈 Portfolio positions ({$positions->count()}):");
            foreach ($positions as $p) {
                $this->info(sprintf('   %-6s | %8s shares @ %8s',
                    $p->symbol,
                    number_format($p->quantity, 2),
                    number_format($p->average_price, 2)
                ));
            }
        }

        return 0;
    }
}
