<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $phone_number
 * @property float $service_percent
 * @property float $tip_percent
 * @property array $people
 * @property array $items
 * @property array $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|Bill create(array $attributes = [])
 */
class Bill extends Model
{
    protected $fillable = [
        'phone_number',
        'service_percent',
        'tip_percent',
        'people',
        'items',
        'result',
    ];

    protected $casts = [
        'service_percent' => 'decimal:2',
        'tip_percent' => 'decimal:2',
        'people' => 'array',
        'items' => 'array',
        'result' => 'array',
    ];
}
