<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * User session — tracks browsing sessions across page views.
 */
class Session extends Model
{
    protected string $table = 'analytics_sessions';

    protected array $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'os',
        'platform',
        'country',
        'city',
        'referrer',
        'landing_page',
        'exit_page',
        'page_views',
        'duration',
        'is_bounce',
        'is_bot',
        'started_at',
        'last_activity_at',
    ];

    protected array $casts = [
        'is_bounce' => 'boolean',
        'is_bot' => 'boolean',
        'page_views' => 'integer',
        'duration' => 'integer',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];
}
