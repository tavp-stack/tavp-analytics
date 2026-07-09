<?php

declare(strict_types=1);

namespace Tavp\Analytics\Support;

/**
 * User agent parser for device, browser, and OS detection.
 * More comprehensive than the TALL version — detects bots, crawlers, and screen resolution.
 */
class UserAgentParser
{
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python-requests',
        'go-http-client', 'java/', 'php/', 'ruby/', 'scrapy', 'headless',
        'phantom', 'selenium', 'puppeteer', 'playwright',
    ];

    private const MOBILE_PATTERNS = [
        'android', 'iphone', 'ipod', 'windows phone', 'blackberry',
        'opera mini', 'opera mobi', 'mobile', 'webos', 'kindle',
    ];

    private const TABLET_PATTERNS = [
        'ipad', 'tablet', 'kindle', 'playbook', 'silk',
    ];

    private const BROWSER_PATTERNS = [
        'Edge' => ['edg', 'edge'],
        'Opera' => ['opr', 'opera'],
        'Chrome' => ['chrome', 'chromium'],
        'Safari' => ['safari'],
        'Firefox' => ['firefox', 'fxios'],
        'IE' => ['msie', 'trident'],
        'Brave' => ['brave'],
    ];

    private const OS_PATTERNS = [
        'Windows' => ['windows', 'win32', 'win64'],
        'macOS' => ['macintosh', 'mac os', 'darwin'],
        'Linux' => ['linux', 'ubuntu', 'debian', 'centos', 'fedora'],
        'Android' => ['android'],
        'iOS' => ['iphone', 'ipad', 'ipod'],
        'ChromeOS' => ['chrome os', 'crOS'],
    ];

    /**
     * Parse a user agent string into device, browser, OS, and bot detection.
     *
     * @return array{device: string, browser: string, os: string, is_bot: bool, bot_name: ?string, platform: string}
     */
    public static function parse(string $userAgent): array
    {
        $lower = strtolower($userAgent);

        return [
            'device' => self::parseDevice($lower),
            'browser' => self::parseBrowser($lower),
            'os' => self::parseOS($lower),
            'is_bot' => self::isBot($lower),
            'bot_name' => self::getBotName($lower),
            'platform' => self::parsePlatform($userAgent),
        ];
    }

    public static function parseDevice(string $ua): string
    {
        foreach (self::TABLET_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return 'tablet';
            }
        }

        foreach (self::MOBILE_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return 'mobile';
            }
        }

        return 'desktop';
    }

    public static function parseBrowser(string $ua): string
    {
        foreach (self::BROWSER_PATTERNS as $name => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($ua, $pattern)) {
                    return $name;
                }
            }
        }

        return 'other';
    }

    public static function parseOS(string $ua): string
    {
        foreach (self::OS_PATTERNS as $name => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($ua, $pattern)) {
                    return $name;
                }
            }
        }

        return 'other';
    }

    public static function isBot(string $ua): bool
    {
        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public static function getBotName(string $ua): ?string
    {
        $bots = [
            'Googlebot' => 'googlebot',
            'Bingbot' => 'bingbot',
            'Slurp' => 'slurp',
            'DuckDuckBot' => 'duckduckbot',
            'Baiduspider' => 'baiduspider',
            'YandexBot' => 'yandexbot',
            'facebot' => 'facebot',
            'ia_archiver' => 'ia_archiver',
        ];

        foreach ($bots as $name => $pattern) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Parse platform from user agent (web, ios, android, desktop).
     */
    public static function parsePlatform(string $ua): string
    {
        $lower = strtolower($ua);

        if (str_contains($lower, 'android')) {
            return 'android';
        }
        if (str_contains($lower, 'iphone') || str_contains($lower, 'ipad') || str_contains($lower, 'ipod')) {
            return 'ios';
        }
        if (preg_match('/tavp[-_]?mobile/i', $ua)) {
            return 'mobile-app';
        }
        if (preg_match('/tavp[-_]?desktop/i', $ua)) {
            return 'desktop-app';
        }

        return 'web';
    }

    /**
     * Extract screen resolution from common patterns in UA or headers.
     */
    public static function parseResolution(string $ua, array $headers = []): ?string
    {
        // Some mobile apps send resolution in headers
        if (isset($headers['X-Screen-Resolution'])) {
            return $headers['X-Screen-Resolution'];
        }

        // Try to extract from UA (some apps embed this)
        if (preg_match('/(\d{3,4})x(\d{3,4})/', $ua, $matches)) {
            return $matches[1] . 'x' . $matches[2];
        }

        return null;
    }
}
