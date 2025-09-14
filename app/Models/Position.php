<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Position where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Position find(int|string $id, array $columns = ['*'])
 * @method static Position create(array $attributes = [])
 * @method static Position|null first(array $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Builder|Position orWhere(string $column, mixed $operator = null, mixed $value = null)
 * @property int $id
 * @property int $portfolio_id
 * @property string $symbol
 * @property float $quantity
 * @property float $average_price
 * @property string $currency
 * @property string|null $isin
 * @property Portfolio $portfolio
 */
class Position extends Model
{
    /** @use HasFactory<\Database\Factories\PositionFactory> */
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'symbol',
        'name',
        'quantity',
        'average_price',
        'currency',
        'isin',
    ];

    public function portfolio(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
