<?php

declare(strict_types=1);

namespace Tavp\Analytics\Http\Middleware;

use Tavp\Analytics\Tracking\PageViewTracker;

/**
 * Middleware that automatically tracks page views on every GET request.
 * Works across web, mobile, and desktop platforms.
 */
class TrackPageView
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function handle(callable $next): mixed
    {
        // Only track GET requests
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            return $next();
        }

        // Skip AJAX and Livewire
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || !empty($_SERVER['HTTP_X_LIVEWIRE'])) {
            return $next();
        }

        // Execute the request first, then track (after response)
        $result = $next();

        try {
            $tracker = new PageViewTracker($this->config);
            $tracker->track([
                'path' => $_SERVER['REQUEST_URI'] ?? '/',
                'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
                'session_id' => $_COOKIE['tavp_session'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'platform' => 'web',
            ]);
        } catch (\Throwable $e) {
            // Silently fail — tracking should never crash the app
        }

        return $result;
    }
}
