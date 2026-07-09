<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * A/B test experiment — tracks experiment assignments and conversions.
 */
class Experiment extends Model
{
    protected string $table = 'analytics_experiments';

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'variants',
        'traffic_percentage',
        'is_active',
        'started_at',
        'ended_at',
        'created_at',
    ];

    protected array $casts = [
        'variants' => 'json',
        'traffic_percentage' => 'float',
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
