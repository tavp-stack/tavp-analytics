<?php

declare(strict_types=1);

namespace Tavp\Analytics\Http\Controllers;

use Tavp\Analytics\Fraud\FraudDetector;
use Tavp\Analytics\Fraud\FraudlessVerifier;
use Tavp\Analytics\Models\AnalyticsEvent;
use Tavp\Analytics\Models\FraudEvent;
use Tavp\Analytics\Models\PageVisit;
use Tavp\Analytics\Models\SessionRecording;
use Tavp\Analytics\Tracking\EventTracker;
use Tavp\Analytics\Tracking\PageViewTracker;
use Tavp\Analytics\Tracking\SessionRecorder;

/**
 * API controller for analytics tracking endpoints.
 * Receives tracking data from web, mobile, and desktop clients.
 */
class TrackingController
{
    /**
     * POST /api/analytics/track
     * Track a page view.
     */
    public function track(): string
    {
        $data = $this->getJsonInput();

        $tracker = new PageViewTracker($this->getConfig());
        $visit = $tracker->track($data);

        return json_encode(['status' => 'ok', 'id' => $visit?->id]);
    }

    /**
     * POST /api/analytics/event
     * Track a custom event.
     */
    public function event(): string
    {
        $data = $this->getJsonInput();

        // Run fraud detection on events
        $fraudScore = 0.0;
        $isSuspicious = false;

        if ($this->getConfig('fraud_detection_enabled', true)) {
            $detector = new FraudDetector($this->getConfig('fraud', []));
            $fraudResult = $detector->analyze($data);
            $fraudScore = $fraudResult['score'];
            $isSuspicious = $fraudResult['is_suspicious'];
        }

        $data['fraud_score'] = $fraudScore;
        $data['is_suspicious'] = $isSuspicious;

        $tracker = new EventTracker($this->getConfig());
        $event = $tracker->track($data);

        return json_encode(['status' => 'ok', 'id' => $event?->id]);
    }

    /**
     * POST /api/analytics/session
     * Save a session recording.
     */
    public function session(): string
    {
        $data = $this->getJsonInput();

        $recorder = new SessionRecorder($this->getConfig('session_recording', []));
        $recording = $recorder->save($data);

        return json_encode(['status' => 'ok', 'id' => $recording?->id]);
    }

    /**
     * POST /api/analytics/verify
     * Verify data authenticity (fraudless check).
     */
    public function verify(): string
    {
        $data = $this->getJsonInput();

        $verifier = new FraudlessVerifier();
        $result = $verifier->verify($data);

        return json_encode(['status' => 'ok', 'result' => $result]);
    }

    /**
     * GET /api/analytics/stats
     * Get analytics statistics.
     */
    public function stats(): string
    {
        $stats = new \Tavp\Analytics\Analytics\StatsEngine();

        return json_encode(['status' => 'ok', 'stats' => $stats->getStats()]);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    private function getConfig(string $key = null, mixed $default = null): mixed
    {
        $config = [
            'session_duration' => 30,
            'exclude_paths' => ['api/*', '_debugbar/*'],
            'exclude_ips' => ['127.0.0.1', '::1'],
            'geolocation_enabled' => false,
            'fraud_detection_enabled' => false,
            'track_events' => true,
            'fraud' => [
                'bot_detection' => true,
                'velocity_limit' => 100,
                'velocity_window' => 60,
                'anomaly_threshold' => 3.0,
                'block_suspicious' => false,
            ],
            'session_recording' => [
                'enabled' => false,
                'max_events' => 10000,
            ],
        ];

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}
