<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

/**
 * Detects click fraud — rapid, inhuman click patterns.
 */
class ClickFraudRule implements FraudRule
{
    private const CLICK_WINDOW = 5;
    private const MAX_CLICKS = 20;

    public function getName(): string
    {
        return 'click_fraud';
    }

    public function evaluate(array $data, array $config): array
    {
        // Only applies to click events
        $eventName = $data['event_name'] ?? '';
        if (!in_array($eventName, ['click', 'tap', 'cta_click', 'button_click'], true)) {
            return ['score' => 0.0, 'reason' => '', 'data' => []];
        }

        $sessionId = $data['session_id'] ?? '';
        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');

        if (empty($sessionId) && empty($ip)) {
            return ['score' => 0.0, 'reason' => '', 'data' => []];
        }

        // Count recent clicks from this session/IP
        $identifier = $sessionId ?: $ip;
        $cacheFile = storage_path('cache/clicks_' . md5($identifier) . '.json');

        $clicks = [];
        if (is_file($cacheFile)) {
            $clicks = json_decode(file_get_contents($cacheFile), true) ?? [];
        }

        // Remove old clicks
        $cutoff = time() - self::CLICK_WINDOW;
        $clicks = array_filter($clicks, fn ($t) => $t > $cutoff);

        // Add current click
        $clicks[] = time();

        // Save
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($cacheFile, json_encode(array_values($clicks)));

        $count = count($clicks);

        if ($count > self::MAX_CLICKS) {
            $severity = min(1.0, ($count - self::MAX_CLICKS) / self::MAX_CLICKS);

            return [
                'score' => 0.6 + ($severity * 0.3),
                'reason' => "Click fraud: {$count} clicks in " . self::CLICK_WINDOW . "s",
                'data' => ['click_count' => $count, 'window' => self::CLICK_WINDOW],
            ];
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}
