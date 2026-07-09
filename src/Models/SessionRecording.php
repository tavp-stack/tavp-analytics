<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Session recording — stores click and scroll events for replay.
 */
class SessionRecording extends Model
{
    protected string $table = 'analytics_session_recordings';

    protected array $fillable = [
        'session_id',
        'user_id',
        'events',
        'duration',
        'viewport_width',
        'viewport_height',
        'started_at',
        'created_at',
    ];

    protected array $casts = [
        'events' => 'json',
        'duration' => 'integer',
        'viewport_width' => 'integer',
        'viewport_height' => 'integer',
        'started_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
