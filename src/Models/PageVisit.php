<?php

declare(strict_types=1);

namespace Tavp\Analytics\Models;

use Tavp\Core\Database\Model;

/**
 * Page visit record — tracks every page view across platforms.
 */
class PageVisit extends Model
{
    protected string $table = 'analytics_page_visits';

    protected array $fillable = [
        'path',
        'title',
        'ip_address',
        'user_agent',
        'referrer',
        'country',
        'city',
        'region',
        'latitude',
        'longitude',
        'timezone',
        'isp',
        'device',
        'browser',
        'os',
        'platform',
        'screen_resolution',
        'session_id',
        'user_id',
        'duration',
        'is_bounce',
        'is_bot',
        'bot_name',
        'is_authenticated',
        'metadata',
        'visited_at',
    ];

    protected array $casts = [
        'is_bounce' => 'boolean',
        'is_bot' => 'boolean',
        'is_authenticated' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'duration' => 'integer',
        'metadata' => 'json',
        'visited_at' => 'datetime',
    ];

    /**
     * Scope: visits from today.
     */
    public function scopeToday(): array
    {
        return $this->where('visited_at', '>=', date('Y-m-d 00:00:00'))
            ->orderBy('visited_at', 'desc')
            ->get();
    }

    /**
     * Scope: visits from this month.
     */
    public function scopeThisMonth(): array
    {
        return $this->where('visited_at', '>=', date('Y-m-01 00:00:00'))
            ->orderBy('visited_at', 'desc')
            ->get();
    }

    /**
     * Scope: real-time visits (last N minutes).
     */
    public function scopeRealtime(int $minutes = 5): array
    {
        return $this->where('visited_at', '>=', date('Y-m-d H:i:s', strtotime("-{$minutes} minutes")))
            ->orderBy('visited_at', 'desc')
            ->get();
    }

    /**
     * Scope: non-bot visits only.
     */
    public function scopeHuman(): array
    {
        return $this->where('is_bot', false)
            ->orderBy('visited_at', 'desc')
            ->get();
    }
}
