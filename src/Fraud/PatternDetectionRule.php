<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

/**
 * Detects known fraud patterns (credential stuffing, scraping, etc.).
 */
class PatternDetectionRule implements FraudRule
{
    private const SUSPICIOUS_PATHS = [
        '/wp-admin', '/wp-login', '/xmlrpc.php',
        '/.env', '/.git', '/config.php', '/wp-config.php',
        '/admin', '/administrator', '/phpmyadmin',
        '/cgi-bin', '/scripts', '/shell',
    ];

    private const SUSPICIOUS_PARAMS = [
        'union', 'select', 'insert', 'update', 'delete', 'drop',
        '<script', 'javascript:', 'onerror', 'onload',
        '../', '..\\', '%00', '%0a', '%0d',
    ];

    public function getName(): string
    {
        return 'pattern_detection';
    }

    public function evaluate(array $data, array $config): array
    {
        $path = $data['path'] ?? '/';
        $params = $data['query_params'] ?? [];

        $indicators = [];

        // Check 1: Accessing known suspicious paths
        foreach (self::SUSPICIOUS_PATHS as $suspicious) {
            if (str_starts_with($path, $suspicious)) {
                $indicators[] = 'suspicious_path';
                break;
            }
        }

        // Check 2: SQL injection patterns in parameters
        $allInput = array_merge($params, $data);
        foreach ($allInput as $key => $value) {
            if (!is_string($value)) {
                continue;
            }
            foreach (self::SUSPICIOUS_PARAMS as $pattern) {
                if (str_contains(strtolower($value), $pattern)) {
                    $indicators[] = 'injection_attempt';
                    break 2;
                }
            }
        }

        // Check 3: Path traversal
        if (str_contains($path, '..') || str_contains($path, '%2e%2e')) {
            $indicators[] = 'path_traversal';
        }

        if (!empty($indicators)) {
            $score = 0.0;
            if (in_array('injection_attempt', $indicators)) {
                $score += 0.8;
            }
            if (in_array('path_traversal', $indicators)) {
                $score += 0.6;
            }
            if (in_array('suspicious_path', $indicators)) {
                $score += 0.3;
            }

            return [
                'score' => min(1.0, $score),
                'reason' => 'Suspicious pattern detected: ' . implode(', ', $indicators),
                'data' => ['indicators' => $indicators, 'path' => $path],
            ];
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}
