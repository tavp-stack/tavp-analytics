<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Funnel step event — records when a user completes a funnel step.
 */
class FunnelEvent extends Model
{
    protected string $table = 'analytics_funnel_events';

    protected array $fillable = [
        'funnel_id',
        'session_id',
        'user_id',
        'step_index',
        'step_name',
        'metadata',
        'created_at',
    ];

    protected array $casts = [
        'step_index' => 'integer',
        'metadata' => 'json',
        'created_at' => 'datetime',
    ];
}
