<?php

declare(strict_types=1);

namespace Tavp\Analytics\Support;

/**
 * IP-based geolocation using free ip-api.com service.
 * Includes caching to avoid rate limiting.
 */
class Geolocator
{
    private static array $cache = [];
    private static string $cachePath = '';

    public static function init(string $cachePath = ''): void
    {
        self::$cachePath = $cachePath;
    }

    /**
     * Get location data for an IP address.
     *
     * @return array{country: ?string, city: ?string, region: ?string, lat: ?float, lon: ?float, timezone: ?string, isp: ?string}
     */
    public static function locate(string $ip): array
    {
        if (in_array($ip, ['127.0.0.1', '::1', ''], true)) {
            return self::emptyLocation();
        }

        // Check cache
        $cached = self::getFromCache($ip);
        if ($cached !== null) {
            return $cached;
        }

        // Fetch from ip-api.com
        $location = self::fetchFromApi($ip);

        // Cache for 24 hours
        self::storeInCache($ip, $location);

        return $location;
    }

    private static function fetchFromApi(string $ip): array
    {
        $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,timezone,isp";

        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'method' => 'GET',
                'header' => "User-Agent: TAVP-Analytics/1.0\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return self::emptyLocation();
        }

        $data = json_decode($response, true);

        if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
            return self::emptyLocation();
        }

        return [
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'region' => $data['regionName'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lon' => $data['lon'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'isp' => $data['isp'] ?? null,
        ];
    }

    private static function emptyLocation(): array
    {
        return [
            'country' => null,
            'city' => null,
            'region' => null,
            'lat' => null,
            'lon' => null,
            'timezone' => null,
            'isp' => null,
        ];
    }

    private static function getFromCache(string $ip): ?array
    {
        // Memory cache
        if (isset(self::$cache[$ip])) {
            return self::$cache[$ip];
        }

        // File cache
        if (self::$cachePath !== '') {
            $file = self::$cachePath . '/geo_' . md5($ip) . '.json';
            if (is_file($file) && (time() - filemtime($file)) < 86400) {
                $data = json_decode(file_get_contents($file), true);
                self::$cache[$ip] = $data;

                return $data;
            }
        }

        return null;
    }

    private static function storeInCache(string $ip, array $location): void
    {
        self::$cache[$ip] = $location;

        if (self::$cachePath !== '' && is_dir(self::$cachePath)) {
            $file = self::$cachePath . '/geo_' . md5($ip) . '.json';
            file_put_contents($file, json_encode($location));
        }
    }
}
