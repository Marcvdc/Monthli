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
    ];

    protected $casts = [
        'month' => 'date',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}
