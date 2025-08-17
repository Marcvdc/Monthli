<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTick extends Model
{
    /** @use HasFactory<\Database\Factories\PriceTickFactory> */
    use HasFactory;

    protected $fillable = [
        'symbol',
        'date',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
