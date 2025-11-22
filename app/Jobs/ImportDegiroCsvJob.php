<?php

namespace App\Jobs;

use App\Models\Portfolio;
use App\Services\DegiroImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportDegiroCsvJob implements ShouldQueue
{
    use Queueable;

    protected string $csvFilePath;
    protected Portfolio $portfolio;

    /**
     * Create a new job instance.
     */
    public function __construct(string $csvFilePath, Portfolio $portfolio)
    {
        $this->csvFilePath = $csvFilePath;
        $this->portfolio = $portfolio;
    }

    /**
     * Execute the job.
     */
    public function handle(DegiroImportService $importService): void
    {
        try {
            Log::info('Starting DEGIRO CSV import', [
                'portfolio_id' => $this->portfolio->id,
                'file_path' => $this->csvFilePath,
            ]);

            // Import CSV using the absolute file path we stored when dispatching the job
            $results = $importService->importCsv($this->csvFilePath, $this->portfolio);

            Log::info('DEGIRO CSV import completed', [
                'portfolio_id' => $this->portfolio->id,
                'success' => $results['success'],
                'duplicates' => $results['duplicates'],
                'errors_count' => count($results['errors']),
            ]);

            // Clean up the temporary file
            if (file_exists($this->csvFilePath)) {
                unlink($this->csvFilePath);
            }
        } catch (\Exception $e) {
            Log::error('DEGIRO CSV import failed', [
                'portfolio_id' => $this->portfolio->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up the temporary file on error too
            if (file_exists($this->csvFilePath)) {
                unlink($this->csvFilePath);
            }

            throw $e;
        }
    }

}
