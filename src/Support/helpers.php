<?php

declare(strict_types=1);

namespace Tavp\Analytics\Support;

if (!function_exists('tavp_analytics_event')) {
    /**
     * Track a custom analytics event from PHP.
     */
    function tavp_analytics_event(string $name, ?string $category = null, ?string $label = null, mixed $value = null, array $metadata = []): void
    {
        $tracker = new \Tavp\Analytics\Tracking\EventTracker();
        $tracker->track([
            'event_name' => $name,
            'event_category' => $category,
            'event_label' => $label,
            'event_value' => $value,
            'metadata' => $metadata,
            'session_id' => $_COOKIE['tavp_session'] ?? null,
        ]);
    }
}

if (!function_exists('tavp_analytics_enabled')) {
    /**
     * Check if analytics tracking is enabled.
     */
    function tavp_analytics_enabled(): bool
    {
        return (bool) (config('analytics.enabled') ?? true);
    }
}
