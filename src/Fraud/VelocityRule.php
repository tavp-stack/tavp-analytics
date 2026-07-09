<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

use Tavp\Analytics\Models\PageVisit;

/**
 * Detects velocity attacks — too many requests in a short time window.
 */
class VelocityRule implements FraudRule
{
    private int $limit;
    private int $windowSeconds;

    public function __construct(int $limit = 100, int $windowSeconds = 60)
    {
        $this->limit = $limit;
        $this->windowSeconds = $windowSeconds;
    }

    public function getName(): string
    {
        return 'velocity';
    }

    public function evaluate(array $data, array $config): array
    {
        $ip = $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');

        if (empty($ip)) {
            return ['score' => 0.0, 'reason' => '', 'data' => []];
        }

        // Count requests from this IP in the window
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$this->windowSeconds} seconds"));

        // In production, use a database query or Redis counter
        // For now, use a simple file-based counter
        $cacheFile = storage_path('cache/velocity_' . md5($ip) . '.json');

        $count = 0;
        if (is_file($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached) && $cached['reset_at'] > time()) {
                $count = $cached['count'];
            }
        }

        $count++;

        // Update cache
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($cacheFile, json_encode([
            'count' => $count,
            'reset_at' => time() + $this->windowSeconds,
        ]));

        if ($count > $this->limit) {
            $severity = min(1.0, ($count - $this->limit) / $this->limit);

            return [
                'score' => 0.5 + ($severity * 0.4),
                'reason' => "Velocity limit exceeded: {$count} requests in {$this->windowSeconds}s (limit: {$this->limit})",
                'data' => ['count' => $count, 'limit' => $this->limit, 'window' => $this->windowSeconds],
            ];
        }

        return ['score' => 0.0, 'reason' => '', 'data' => []];
    }
}
