<?php

declare(strict_types=1);

namespace Tavp\Analytics\Tracking;

use Tavp\Analytics\Models\AnalyticsEvent;

/**
 * Tracks custom events with metadata and fraud scoring.
 */
class EventTracker
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'track_events' => true,
            'exclude_paths' => [],
            'exclude_ips' => [],
        ], $config);
    }

    /**
     * Record a custom analytics event.
     */
    public function track(array $data): ?AnalyticsEvent
    {
        if (!$this->config['track_events']) {
            return null;
        }

        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');

        if (in_array($ip, $this->config['exclude_ips'], true)) {
            return null;
        }

        $event = new AnalyticsEvent();
        $event->fill([
            'event_name' => $data['event_name'],
            'event_category' => $data['event_category'] ?? null,
            'event_label' => $data['event_label'] ?? null,
            'event_value' => $data['event_value'] ?? null,
            'path' => $data['path'] ?? ($_SERVER['REQUEST_URI'] ?? '/'),
            'ip_address' => $ip,
            'session_id' => $data['session_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'platform' => $data['platform'] ?? 'web',
            'metadata' => $data['metadata'] ?? null,
            'fraud_score' => 0.0,
            'is_suspicious' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $event->save();

        return $event;
    }
}
