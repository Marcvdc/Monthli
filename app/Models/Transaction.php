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
        'quantity',
        'price',
        'executed_at',
        'external_id',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
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
