<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Custom analytics event — tracks user actions beyond page views.
 */
class AnalyticsEvent extends Model
{
    protected string $table = 'analytics_events';

    protected array $fillable = [
        'event_name',
        'event_category',
        'event_label',
        'event_value',
        'path',
        'ip_address',
        'session_id',
        'user_id',
        'platform',
        'metadata',
        'fraud_score',
        'is_suspicious',
        'created_at',
    ];

    protected array $casts = [
        'metadata' => 'json',
        'fraud_score' => 'float',
        'is_suspicious' => 'boolean',
        'created_at' => 'datetime',
    ];
}
