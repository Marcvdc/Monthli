<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlySnapshot extends Model
{
    /** @use HasFactory<\Database\Factories\MonthlySnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'month',
        'value',
        'mom',
        'ytd',
        'drawdown',
        'volatility',
    ];

    protected $casts = [
        'month' => 'date',
        'value' => 'float',
        'mom' => 'float',
        'ytd' => 'float',
        'drawdown' => 'float',
        'volatility' => 'float',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
