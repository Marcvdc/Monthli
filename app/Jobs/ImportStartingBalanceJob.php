<?php

namespace App\Jobs;

use App\Models\Portfolio;
use App\Services\StartingBalanceImportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportStartingBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public int $portfolioId,
        public string $balanceDate,
        public ?string $uploadedBy = null
    ) {}

    public function handle(StartingBalanceImportService $importService): void
    {
        $portfolio = Portfolio::findOrFail($this->portfolioId);
        $balanceDate = Carbon::parse($this->balanceDate);

        Log::info('Starting balance import job started', [
            'portfolio_id' => $this->portfolioId,
            'balance_date' => $this->balanceDate,
            'file_path' => $this->filePath,
            'uploaded_by' => $this->uploadedBy
        ]);

        try {
            $results = $importService->importStartingBalance($this->filePath, $portfolio, $balanceDate);

            Log::info('Starting balance import job completed', [
                'portfolio_id' => $this->portfolioId,
                'results' => $results
            ]);

            // Clean up temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

        } catch (\Exception $e) {
            Log::error('Starting balance import job failed', [
                'portfolio_id' => $this->portfolioId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up temporary file on failure too
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            throw $e;
        }
    }
}
