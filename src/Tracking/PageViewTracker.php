<?php

declare(strict_types=1);

namespace Tavp\Analytics\Tracking;

use Tavp\Analytics\Models\PageVisit;
use Tavp\Analytics\Models\Session;
use Tavp\Analytics\Support\Geolocator;
use Tavp\Analytics\Support\UserAgentParser;

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
        $parsed = UserAgentParser::parse($ua);

        // Geolocation
        $location = ['country' => null, 'city' => null, 'region' => null, 'lat' => null, 'lon' => null, 'timezone' => null, 'isp' => null];
        if ($this->config['geolocation_enabled'] && !empty($ip)) {
            $location = Geolocator::locate($ip);
        }

        // Session management
        $sessionId = $data['session_id'] ?? $this->generateSessionId();
        $session = $this->resolveSession($sessionId, $data, $ip, $ua, $parsed, $location);

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
            'screen_resolution' => $data['screen_resolution'] ?? UserAgentParser::parseResolution($ua, $_SERVER),
            'session_id' => $sessionId,
            'user_id' => $data['user_id'] ?? null,
            'duration' => $data['duration'] ?? 0,
            'is_bounce' => ($data['duration'] ?? 0) < 30 && ($data['page_views_in_session'] ?? 1) <= 1,
            'is_bot' => $parsed['is_bot'],
            'bot_name' => $parsed['bot_name'],
            'is_authenticated' => !empty($data['user_id']),
            'metadata' => $data['metadata'] ?? null,
            'visited_at' => $data['visited_at'] ?? date('Y-m-d H:i:s'),
        ]);

        $visit->save();

        // Update session
        if ($session !== null) {
            $this->updateSession($session, $data);
        }

        return $visit;
    }

    private function shouldTrack(array $data): bool
    {
        $path = $data['path'] ?? '/';
        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');

        // Exclude paths
        foreach ($this->config['exclude_paths'] as $pattern) {
            if (fnmatch($pattern, $path)) {
                return false;
            }
        }

        // Exclude IPs
        if (in_array($ip, $this->config['exclude_ips'], true)) {
            return false;
        }

        return true;
    }

    private function resolveSession(string $sessionId, array $data, string $ip, string $ua, array $parsed, array $location): ?Session
    {
        // Look for existing session
        $session = Session::findFirst([
            'conditions' => 'session_id = :session_id AND last_activity_at >= :cutoff',
            'bind' => [
                'session_id' => $sessionId,
                'cutoff' => date('Y-m-d H:i:s', strtotime("-{$this->config['session_duration']} minutes")),
            ],
        ]);

        if ($session !== null) {
            return $session;
        }

        // Create new session
        $session = new Session();
        $session->fill([
            'session_id' => $sessionId,
            'user_id' => $data['user_id'] ?? null,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'os' => $parsed['os'],
            'platform' => $data['platform'] ?? $parsed['platform'],
            'country' => $location['country'],
            'city' => $location['city'],
            'referrer' => $data['referrer'] ?? null,
            'landing_page' => $data['path'] ?? '/',
            'page_views' => 1,
            'duration' => 0,
            'is_bounce' => true,
            'is_bot' => $parsed['is_bot'],
            'started_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $session->save();

        return $session;
    }

    private function updateSession(Session $session, array $data): void
    {
        $pageViews = ($session->page_views ?? 0) + 1;
        $duration = $data['duration'] ?? 0;

        $session->fill([
            'exit_page' => $data['path'] ?? '/',
            'page_views' => $pageViews,
            'duration' => $duration,
            'is_bounce' => $pageViews <= 1 && $duration < 30,
            'last_activity_at' => date('Y-m-d H:i:s'),
        ]);

        $session->save();
    }

    private function generateSessionId(): string
    {
        return 'sess_' . bin2hex(random_bytes(12)) . '_' . time();
    }
}
