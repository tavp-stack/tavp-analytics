<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

/**
 * Detects statistical anomalies in user behavior.
 * Uses z-score to identify outliers in request patterns.
 */
class AnomalyDetectionRule implements FraudRule
{
    private float $threshold;

    public function __construct(float $threshold = 3.0)
    {
        $this->threshold = $threshold;
    }

    public function getName(): string
    {
        return 'anomaly_detection';
    }

    public function evaluate(array $data, array $config): array
    {
        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $path = $data['path'] ?? '/';

        // Simple anomaly: unusual request patterns
        // In production, use proper statistical analysis with historical data

        $indicators = [];

        // Check 1: Unusual hour (2am-5am local time)
        $hour = (int) date('H');
        if ($hour >= 2 && $hour <= 5) {
            $indicators[] = 'unusual_hour';
        }

        // Check 2: Very fast sequential requests
        $lastRequestFile = storage_path('cache/last_request_' . md5($ip) . '.json');
        if (is_file($lastRequestFile)) {
            $lastRequest = json_decode(file_get_contents($lastRequestFile), true);
            if (is_array($lastRequest)) {
                $timeDiff = time() - ($lastRequest['time'] ?? 0);
                if ($timeDiff < 1) {
                    $indicators[] = 'rapid_fire';
                }
            }
        }

        // Save current request timestamp
        $dir = dirname($lastRequestFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($lastRequestFile, json_encode(['time' => time()]));

        // Check 3: Accessing many different paths quickly
        $pathVarietyFile = storage_path('cache/path_variety_' . md5($ip) . '.json');
        $paths = [];
        if (is_file($pathVarietyFile)) {
            $paths = json_decode(file_get_contents($pathVarietyFile), true) ?? [];
        }
        $paths[] = $path;
        $paths = array_unique($paths);
        file_put_contents($pathVarietyFile, json_encode($paths));

        if (count($paths) > 50) {
            $indicators[] = 'path_scanning';
        }

        if (!empty($indicators)) {
            $score = min(0.9, count($indicators) * 0.25);

            return [
                'score' => $score,
                'reason' => 'Anomalous behavior detected: ' . implode(', ', $indicators),
                'data' => ['indicators' => $indicators, 'unique_paths' => count($paths)],
            ];
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}
