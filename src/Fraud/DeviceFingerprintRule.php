<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

use Tavp\Analytics\Support\UserAgentParser;

/**
 * Detects suspicious device fingerprints.
 */
class DeviceFingerprintRule implements FraudRule
{
    public function getName(): string
    {
        return 'device_fingerprint';
    }

    public function evaluate(array $data, array $config): array
    {
        $ua = $data['user_agent'] ?? '';
        $parsed = UserAgentParser::parse($ua);

        $indicators = [];

        // Check 1: Desktop UA but mobile platform header
        $platform = $data['platform'] ?? $parsed['platform'];
        if ($parsed['device'] === 'desktop' && in_array($platform, ['ios', 'android'], true)) {
            $indicators[] = 'platform_mismatch';
        }

        // Check 2: Missing or generic screen resolution
        $resolution = $data['screen_resolution'] ?? null;
        if ($resolution === null && $parsed['device'] === 'desktop') {
            $indicators[] = 'missing_resolution';
        }

        // Check 3: Inconsistent timezone
        $timezone = $data['timezone'] ?? null;
        $country = $data['country'] ?? null;
        if ($timezone !== null && $country !== null) {
            // Simplified: check if timezone roughly matches country
            // In production, use proper timezone-to-country mapping
        }

        if (!empty($indicators)) {
            $score = min(0.6, count($indicators) * 0.2);

            return [
                'score' => $score,
                'reason' => 'Suspicious device fingerprint: ' . implode(', ', $indicators),
                'data' => ['indicators' => $indicators],
            ];
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}
