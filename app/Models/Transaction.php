<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction find(int|string $id, array $columns = ['*'])
 * @method static Transaction create(array $attributes = [])
 * @method bool exists()
 * @property int $id
 * @property Portfolio $portfolio
 * @property Position $position_id
 * @property string $type
 * @property string|null $symbol
 * @property string|null $isin
 * @property float $quantity
 * @property float $price
 * @property string $currency
 * @property float $total_amount
 * @property float $exchange_rate
 * @property float $fees
 * @property string $venue
 * @property string $description
 * @property \Carbon\Carbon $executed_at
 * @property string $external_id
 * @property array $raw_data
 */
class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'position_id',
        'import_batch_id',
        'source_file_name',
        'source_file_hash',
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'executed_at' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Portfolio, Transaction>
     */
    public function portfolio(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Position, Transaction>
     */
    public function position(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
