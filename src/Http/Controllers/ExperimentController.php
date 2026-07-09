<?php

declare(strict_types=1);

namespace Tavp\Analytics\Http\Controllers;

use Tavp\Analytics\Analytics\ExperimentTracker;
use Tavp\Analytics\Analytics\FunnelAnalyzer;

/**
 * API controller for experiment and funnel tracking.
 */
class ExperimentController
{
    /**
     * GET /api/analytics/experiment/{slug}/variant
     * Get the assigned variant for a session.
     */
    public function variant(string $slug): string
    {
        $sessionId = $_COOKIE['tavp_session'] ?? '';
        $userId = $_COOKIE['tavp_user_id'] ?? null;

        $tracker = new ExperimentTracker();
        $variant = $tracker->getVariant($slug, $sessionId, $userId ? (int) $userId : null);

        return json_encode([
            'status' => 'ok',
            'experiment' => $slug,
            'variant' => $variant,
        ]);
    }

    /**
     * POST /api/analytics/experiment/{slug}/convert
     * Record a conversion.
     */
    public function convert(string $slug): string
    {
        $sessionId = $_COOKIE['tavp_session'] ?? '';

        $tracker = new ExperimentTracker();
        $recorded = $tracker->recordConversion($slug, $sessionId);

        return json_encode([
            'status' => 'ok',
            'recorded' => $recorded,
        ]);
    }

    /**
     * GET /api/analytics/experiment/{slug}/results
     * Get experiment results.
     */
    public function results(string $slug): string
    {
        $tracker = new ExperimentTracker();
        $results = $tracker->getResults($slug);

        return json_encode(['status' => 'ok', 'results' => $results]);
    }

    /**
     * POST /api/analytics/funnel/{slug}/step
     * Record a funnel step.
     */
    public function funnelStep(string $slug): string
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $stepIndex = $data['step'] ?? 0;

        $analyzer = new FunnelAnalyzer();
        $recorded = $analyzer->recordStep($slug, $stepIndex, [
            'session_id' => $_COOKIE['tavp_session'] ?? null,
            'user_id' => $_COOKIE['tavp_user_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);

        return json_encode([
            'status' => 'ok',
            'recorded' => $recorded,
        ]);
    }

    /**
     * GET /api/analytics/funnel/{slug}/results
     * Get funnel analysis results.
     */
    public function funnelResults(string $slug): string
    {
        $analyzer = new FunnelAnalyzer();
        $results = $analyzer->analyze($slug);

        return json_encode(['status' => 'ok', 'results' => $results]);
    }
}
