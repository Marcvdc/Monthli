<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Position;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PortfolioReplayService
{
    public function replay(Portfolio $portfolio): void
    {
        DB::transaction(function () use ($portfolio) {
            $transactions = Transaction::where('portfolio_id', $portfolio->id)
                ->whereIn('type', ['BUY', 'SELL'])
                ->orderBy('executed_at')
                ->orderBy('id')
                ->get();

            if ($transactions->isEmpty()) {
                return;
            }

            // Set balance_date automatically if not set yet (FULL_HISTORY-mode: day before first transaction)
            if (! $portfolio->balance_date) {
                $first = $transactions->first();
                $portfolio->balance_date = $first->executed_at->copy()->subDay()->startOfDay();
                $portfolio->save();
            }

            $aggregates = [];

            foreach ($transactions as $transaction) {
                $key = $this->makeInstrumentKey($transaction);

                if ($key === null) {
                    continue;
                }

                if (! array_key_exists($key, $aggregates)) {
                    $aggregates[$key] = [
                        'symbol' => $transaction->symbol,
                        'isin' => $transaction->isin,
                        'quantity' => 0.0,
                        'average_price' => 0.0,
                    ];
                }

                $currentQty = $aggregates[$key]['quantity'];
                $currentAvg = $aggregates[$key]['average_price'];
                $txQty = (float) $transaction->quantity;
                $txPrice = (float) $transaction->price;

                if ($transaction->type === 'BUY') {
                    $totalCost = ($currentQty * $currentAvg) + ($txQty * $txPrice);
                    $newQty = $currentQty + $txQty;

                    $aggregates[$key]['quantity'] = $newQty;
                    $aggregates[$key]['average_price'] = $newQty > 0 ? $totalCost / $newQty : 0.0;
                } elseif ($transaction->type === 'SELL') {
                    // quantity is negative for sells, average price unchanged
                    $aggregates[$key]['quantity'] = $currentQty + $txQty;
                }
            }

            // Sync aggregates back to positions
            $existingPositions = Position::where('portfolio_id', $portfolio->id)
                ->get()
                ->keyBy(function (Position $position): string {
                    $symbol = $position->symbol ?? '';
                    $isin = $position->isin ?? '';

                    return $symbol.'|'.$isin;
                });

            foreach ($aggregates as $key => $data) {
                $position = $existingPositions->get($key);

                if (! $position) {
                    $position = new Position();
                    $position->portfolio_id = $portfolio->id;
                    $position->symbol = $data['symbol'];
                    $position->isin = $data['isin'];
                }

                $position->quantity = $data['quantity'];
                $position->average_price = $data['average_price'];
                $position->save();
            }
        });
    }

    private function makeInstrumentKey(Transaction $transaction): ?string
    {
        $symbol = $transaction->symbol;
        $isin = $transaction->isin;

        if (! $symbol && ! $isin) {
            return null;
        }

        return ($symbol ?: '').'|'.($isin ?: '');
    }
}
