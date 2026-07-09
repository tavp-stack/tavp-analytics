<?php

declare(strict_types=1);

namespace Tavp\Analytics\Http\Middleware;

use Tavp\Analytics\Fraud\FraudDetector;

/**
 * Middleware that runs fraud detection on incoming requests.
 */
class DetectFraud
{
    private FraudDetector $detector;
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->detector = new FraudDetector($config['fraud'] ?? []);
    }

    public function handle(callable $next): mixed
    {
        $data = [
            'path' => $_SERVER['REQUEST_URI'] ?? '/',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'session_id' => $_COOKIE['tavp_session'] ?? null,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'platform' => $_SERVER['HTTP_X_TAVP_PLATFORM'] ?? 'web',
            'screen_resolution' => $_SERVER['HTTP_X_SCREEN_RESOLUTION'] ?? null,
            'timezone' => $_SERVER['HTTP_X_TIMEZONE'] ?? null,
        ];

        $result = $this->detector->analyze($data);

        // Add fraud result to request headers for downstream use
        $_SERVER['TAVP_FRAUD_SCORE'] = $result['score'];
        $_SERVER['TAVP_FRAUD_SUSPICIOUS'] = $result['is_suspicious'] ? '1' : '0';

        if ($result['should_block'] && ($this->config['fraud']['block_suspicious'] ?? false)) {
            http_response_code(403);
            echo json_encode(['error' => 'Request blocked by fraud detection']);

            return null;
        }

        return $next();
    }
}
