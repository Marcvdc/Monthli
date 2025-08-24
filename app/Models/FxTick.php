<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FxTick extends Model
{
    /** @use HasFactory<\Database\Factories\FxTickFactory> */
    use HasFactory;

    protected $fillable = [
        'base_currency',
        'quote_currency',
        'date',
        'rate',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];
}
