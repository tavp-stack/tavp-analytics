<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Conversion funnel — tracks user progress through defined funnels.
 */
class Funnel extends Model
{
    protected string $table = 'analytics_funnels';

    protected array $fillable = [
        'name',
        'slug',
        'steps',
        'is_active',
        'created_at',
    ];

    protected array $casts = [
        'steps' => 'json',
        'is_active' => 'boolean',
    ];
}
