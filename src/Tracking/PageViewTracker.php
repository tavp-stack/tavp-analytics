<?php

declare(strict_types=1);

namespace Tavp\Analytics\Tracking;

use Tavp\Analytics\Models\PageVisit;

/**
 * Tracks page views and sessions across all platforms.
 * Works with web, mobile, and desktop applications.
 */
class PageViewTracker
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'session_duration' => 30,
            'exclude_paths' => [],
            'exclude_ips' => [],
            'geolocation_enabled' => true,
        ], $config);
    }

    /**
     * Record a page view from any platform.
     */
    public function track(array $data): ?PageVisit
    {
        if (!$this->shouldTrack($data)) {
            return null;
        }

        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $parsed = \Tavp\Analytics\Support\UserAgentParser::parse($ua);

        // Geolocation
        $location = ['country' => null, 'city' => null, 'region' => null, 'lat' => null, 'lon' => null, 'timezone' => null, 'isp' => null];
        if ($this->config['geolocation_enabled'] && !empty($ip)) {
            $location = \Tavp\Analytics\Support\Geolocator::locate($ip);
        }

        // Session management
        $sessionId = $data['session_id'] ?? $this->generateSessionId();

        // Create page visit
        $visit = new PageVisit();
        $visit->fill([
            'path' => $data['path'] ?? '/',
            'title' => $data['title'] ?? null,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'referrer' => $data['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? null),
            'country' => $location['country'],
            'city' => $location['city'],
            'region' => $location['region'],
            'latitude' => $location['lat'],
            'longitude' => $location['lon'],
            'timezone' => $location['timezone'],
            'isp' => $location['isp'],
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'os' => $parsed['os'],
            'platform' => $data['platform'] ?? $parsed['platform'],
            'screen_resolution' => $data['viewport'] ?? null,
            'session_id' => $sessionId,
            'user_id' => $data['user_id'] ?? null,
        ]);

        $visit->save();

        // Track session using raw SQL
        $this->trackSession($sessionId, $data, $ip, $ua, $parsed, $location);

        return $visit;
    }

    private function shouldTrack(array $data): bool
    {
        // Exclude paths
        $path = $data['path'] ?? '/';
        foreach ($this->config['exclude_paths'] as $pattern) {
            if (fnmatch($pattern, $path)) {
                return false;
            }
        }

        // Exclude IPs
        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        if (in_array($ip, $this->config['exclude_ips'], true)) {
            return false;
        }

        return true;
    }

    private function trackSession(string $sessionId, array $data, string $ip, string $ua, array $parsed, array $location): void
    {
        try {
            $db = app('db');
            $now = date('Y-m-d H:i:s');
            $cutoff = date('Y-m-d H:i:s', strtotime("-{$this->config['session_duration']} minutes"));

            // Check if session exists
            $result = $db->query(
                "SELECT id FROM analytics_sessions WHERE session_id = :sid AND last_activity_at >= :cutoff",
                ['sid' => $sessionId, 'cutoff' => $cutoff]
            );
            $rows = $result->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                // Update existing session
                $db->update('analytics_sessions', [
                    'page_views' => new \Phalcon\Db\RawValue('page_views + 1'),
                    'last_activity_at' => $now,
                    'last_page' => $data['path'] ?? '/',
                ], ['id' => $rows[0]['id']]);
            } else {
                // Create new session using raw SQL
                $db->execute(
                    "INSERT INTO analytics_sessions (session_id, user_id, ip_address, user_agent, country, city, device, browser, os, platform, screen_resolution, first_page, last_page, page_views, duration_seconds, bounced, created_at, last_activity_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, 1, ?, ?)",
                    [
                        $sessionId,
                        $data['user_id'] ?? null,
                        $ip,
                        $ua,
                        $location['country'],
                        $location['city'],
                        $parsed['device'],
                        $parsed['browser'],
                        $parsed['os'],
                        $parsed['platform'] ?? null,
                        $data['viewport'] ?? null,
                        $data['path'] ?? '/',
                        $data['path'] ?? '/',
                        $now,
                        $now,
                    ]
                );
            }
        } catch (\Throwable $e) {
            error_log("Session tracking error: " . $e->getMessage());
        }
    }

    private function generateSessionId(): string
    {
        return 'sess_' . bin2hex(random_bytes(12)) . '_' . time();
    }
}
