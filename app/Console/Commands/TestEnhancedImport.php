<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;
use App\Models\User;
use App\Services\DegiroImportService;
use App\Services\StartingBalanceImportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestEnhancedImport extends Command
{
    protected $signature = 'test:enhanced-import {--fresh : Delete existing test data}';
    protected $description = 'Test enhanced DEGIRO import with starting balance and overlapping uploads';

    public function handle(): int
    {
        $this->info('=== Testing Enhanced DEGIRO Import ===');

        if ($this->option('fresh')) {
            $this->info('🗑️  Cleaning up existing test data...');
            $this->cleanupTestData();
        }

        // Step 1: Create test user and portfolio
        $this->info('📁 Creating test portfolio...');
        $portfolio = $this->createTestPortfolio();

        // Step 2: Import starting balance
        $this->info('💰 Testing starting balance import...');
        $this->testStartingBalanceImport($portfolio);

        // Step 3: Import initial transactions
        $this->info('📊 Testing initial transaction import...');
        $this->testInitialTransactionImport($portfolio);

        // Step 4: Test overlapping uploads (duplicate detection)
        $this->info('🔄 Testing overlapping upload detection...');
        $this->testOverlappingUpload($portfolio);

        // Step 5: Show final results
        $this->info('📈 Final portfolio state:');
        $this->showPortfolioState($portfolio);

        $this->info('✅ Enhanced import testing completed successfully!');
        return 0;
    }

    private function cleanupTestData(): void
    {
        DB::table('transactions')->where('description', 'like', 'TEST:%')->delete();
        DB::table('positions')->whereIn('portfolio_id', function($query) {
            $query->select('id')->from('portfolios')->where('name', 'like', 'Test Portfolio%');
        })->delete();
        DB::table('portfolios')->where('name', 'like', 'Test Portfolio%')->delete();
        DB::table('users')->where('email', 'test@enhanced-import.com')->delete();
    }

    private function createTestPortfolio(): Portfolio
    {
        $user = User::firstOrCreate(
            ['email' => 'test@enhanced-import.com'],
            ['name' => 'Test Enhanced Import User', 'password' => bcrypt('password')]
        );

        return Portfolio::create([
            'user_id' => $user->id,
            'name' => 'Test Portfolio Enhanced Import',
            'snapshot_day' => 1,
            'base_currency' => 'EUR'
        ]);
    }

    private function testStartingBalanceImport(Portfolio $portfolio): void
    {
        $csvPath = base_path('tests/Fixtures/csv/degiro_portfolio_sample.csv');
        $balanceDate = Carbon::parse('2024-01-01');
        
        $service = new StartingBalanceImportService();
        $results = $service->importStartingBalance($csvPath, $portfolio, $balanceDate);

        $this->table(['Metric', 'Value'], [
            ['Positions Created', $results['positions_created']],
            ['Successful Rows', $results['success']],
            ['Errors', count($results['errors'])]
        ]);

        if ($results['errors']) {
            $this->warn('Errors found:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        // Verify portfolio balance_date was set
        $portfolio->refresh();
        $this->info("Portfolio balance_date set to: {$portfolio->balance_date}");
    }

    private function testInitialTransactionImport(Portfolio $portfolio): void
    {
        $csvPath = base_path('tests/Fixtures/csv/degiro_sample.csv');
        
        $service = new DegiroImportService();
        $results = $service->importCsv($csvPath, $portfolio);

        $this->table(['Metric', 'Value'], [
            ['Successful Imports', $results['success']],
            ['Duplicates Skipped', $results['duplicates']],
            ['Errors', count($results['errors'])]
        ]);

        if ($results['errors']) {
            $this->warn('Errors found:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }
    }

    private function testOverlappingUpload(Portfolio $portfolio): void
    {
        $csvPath = base_path('tests/Fixtures/csv/degiro_sample.csv');
        
        $this->info('Attempting to import the same CSV again (should detect duplicates)...');
        
        $service = new DegiroImportService();
        $results = $service->importCsv($csvPath, $portfolio);

        $this->table(['Metric', 'Value'], [
            ['New Imports', $results['success']],
            ['Duplicates Detected', $results['duplicates']],
            ['Errors', count($results['errors'])]
        ]);

        if ($results['duplicates'] > 0) {
            $this->info('✅ Duplicate detection working correctly!');
        } else {
            $this->warn('⚠️  Expected duplicates but none were detected');
        }
    }

    private function showPortfolioState(Portfolio $portfolio): void
    {
        $positions = Position::where('portfolio_id', $portfolio->id)->get();
        $transactions = Transaction::where('portfolio_id', $portfolio->id)->get();

        $this->info("\nPositions ({$positions->count()}):");
        $positionData = $positions->map(function($position) {
            return [
                'Symbol' => $position->symbol ?: 'N/A',
                'ISIN' => $position->isin ?: 'N/A', 
                'Name' => $position->name ?: 'N/A',
                'Quantity' => number_format($position->quantity, 2),
                'Avg Price' => '€' . number_format($position->average_price, 2),
                'Value' => '€' . number_format($position->quantity * $position->average_price, 2)
            ];
        });

        if ($positionData->isNotEmpty()) {
            $this->table(['Symbol', 'ISIN', 'Name', 'Quantity', 'Avg Price', 'Value'], $positionData);
        } else {
            $this->line('  No positions found');
        }

        $this->info("\nTransactions ({$transactions->count()}):");
        $transactionData = $transactions->take(10)->map(function($transaction) {
            return [
                'Date' => $transaction->executed_at->format('Y-m-d'),
                'Type' => $transaction->type,
                'Symbol' => $transaction->symbol ?: 'N/A',
                'Quantity' => $transaction->quantity ? number_format($transaction->quantity, 2) : 'N/A',
                'Price' => $transaction->price ? '€' . number_format($transaction->price, 2) : 'N/A',
                'Total' => $transaction->total_amount ? '€' . number_format($transaction->total_amount, 2) : 'N/A'
            ];
        });

        if ($transactionData->isNotEmpty()) {
            $this->table(['Date', 'Type', 'Symbol', 'Quantity', 'Price', 'Total'], $transactionData);
            if ($transactions->count() > 10) {
                $this->line("  ... and " . ($transactions->count() - 10) . " more transactions");
            }
        } else {
            $this->line('  No transactions found');
        }

        // Show balance date coordination
        $portfolio->refresh();
        if ($portfolio->balance_date) {
            $transactionsAfterBalance = $transactions->filter(function($tx) use ($portfolio) {
                return $tx->executed_at->greaterThan($portfolio->balance_date);
            });
            
            $this->info("\nBalance Date Coordination:");
            $this->line("  Portfolio balance date: {$portfolio->balance_date->format('Y-m-d')}");
            $this->line("  Transactions after balance date: {$transactionsAfterBalance->count()}");
            $this->line("  (Only these transactions should have updated position quantities)");
        }
    }
}
