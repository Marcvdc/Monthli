<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dividend extends Model
{
    /** @use HasFactory<\Database\Factories\DividendFactory> */
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'symbol',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
