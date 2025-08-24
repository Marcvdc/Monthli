<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dividend extends Model
{
    /** @use HasFactory<\Database\Factories\DividendFactory> */
    use HasFactory;

    /**
     * @property int $id
     * @property int $portfolio_id
     * @property string $symbol
     * @property string $currency
     * @property float $amount
     * @property \Illuminate\Support\Carbon $received_at
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property Portfolio $portfolio
     */

    protected $fillable = [
        'portfolio_id',
        'symbol',
        'amount',
        'paid_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'paid_at' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Portfolio, Dividend>
     */
    public function portfolio(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
