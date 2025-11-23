<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Portfolio where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Portfolio find(int|string $id, array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Portfolio[] pluck(string $column, string $key = null)
 * @method static Portfolio create(array $attributes = [])
 * @method static \Illuminate\Database\Eloquent\Collection|Portfolio[] get(array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Portfolio[] all(array $columns = ['*'])
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $snapshot_day
 * @property string $base_currency
 */
class Portfolio extends Model
{
    /** @use HasFactory<\Database\Factories\PortfolioFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'snapshot_day',
        'base_currency',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function dividends(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dividend::class);
    }

    public function monthlySnapshots(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MonthlySnapshot::class);
    }
}
