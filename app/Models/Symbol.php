<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $isin
 * @property string $ticker
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Symbol where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Symbol find(int|string $id, array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Symbol first(array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Symbol orWhere(string $column, mixed $operator = null, mixed $value = null)
 * @method static Symbol create(array $attributes = [])
 * @method static mixed value(string $column)
 */
class Symbol extends Model
{
    /** @use HasFactory<\Database\Factories\SymbolFactory> */
    use HasFactory;

    protected $fillable = [
        'isin',
        'ticker',
    ];
}
