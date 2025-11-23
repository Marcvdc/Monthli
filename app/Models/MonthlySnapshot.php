<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlySnapshot extends Model
{
    /** @use HasFactory<\Database\Factories\MonthlySnapshotFactory> */
    use HasFactory;

    /**
     * @property int $id
     * @property int $portfolio_id
     * @property \Illuminate\Support\Carbon $snapshot_date
     * @property float $total_value
     * @property float $total_cost
     * @property float $unrealized_pnl
     * @property float $cash_balance
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property Portfolio $portfolio
     */
    protected $fillable = [
        'portfolio_id',
        'month',
        'value',
        'mom',
        'ytd',
        'drawdown',
        'volatility',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'month' => 'date',
        'value' => 'float',
        'mom' => 'float',
        'ytd' => 'float',
        'drawdown' => 'float',
        'volatility' => 'float',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Portfolio, MonthlySnapshot>
     */
    public function portfolio(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
