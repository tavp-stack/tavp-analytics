<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

use Tavp\Analytics\Support\UserAgentParser;

/**
 * Detects known bots and crawlers.
 */
class BotDetectionRule implements FraudRule
{
    public function getName(): string
    {
        return 'bot_detection';
    }

    public function evaluate(array $data, array $config): array
    {
        $ua = $data['user_agent'] ?? '';

        $parsed = UserAgentParser::parse($ua);

        if ($parsed['is_bot']) {
            return [
                'score' => 0.3,
                'reason' => 'Known bot/crawler detected: ' . ($parsed['bot_name'] ?? 'unknown'),
                'data' => ['bot_name' => $parsed['bot_name']],
            ];
        }

        // Check for empty or suspicious UA
        if (empty($ua) || strlen($ua) < 10) {
            return [
                'score' => 0.4,
                'reason' => 'Empty or suspiciously short user agent',
                'data' => ['ua_length' => strlen($ua)],
            ];
        }

        // Check for headless browser patterns
        $headlessPatterns = ['headless', 'phantom', 'slimer', 'splash'];
        foreach ($headlessPatterns as $pattern) {
            if (str_contains(strtolower($ua), $pattern)) {
                return [
                    'score' => 0.7,
                    'reason' => 'Headless browser detected',
                    'data' => ['pattern' => $pattern],
                ];
            }
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}
