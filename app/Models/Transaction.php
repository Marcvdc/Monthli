<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'position_id',
        'type',
        'symbol',
        'isin',
        'quantity',
        'price',
        'currency',
        'total_amount',
        'exchange_rate',
        'fees',
        'venue',
        'description',
        'executed_at',
        'external_id',
        'raw_data',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
