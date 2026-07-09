<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

/**
 * Fraudless verification — scores data authenticity.
 * Determines if analytics data represents real human behavior.
 *
 * Unlike fraud detection (which finds bad actors), fraudless verification
 * confirms that data IS legitimate. Useful for:
 * - Verifying conversion data is real
 * - Ensuring analytics reports are trustworthy
 * - Validating user engagement metrics
 */
class FraudlessVerifier
{
    private const AUTHENTICITY_SIGNALS = [
        'has_mouse_movement' => 0.15,
        'has_scroll_events' => 0.10,
        'has_focus_events' => 0.10,
        'realistic_duration' => 0.20,
        'natural_navigation' => 0.15,
        'human_like_timing' => 0.15,
        'consistent_device' => 0.15,
    ];

    /**
     * Score the authenticity of a session or event.
     * Returns a score from 0.0 (definitely fake) to 1.0 (definitely real).
     *
     * @return array{score: float, confidence: string, signals: array, recommendation: string}
     */
    public function verify(array $data): array
    {
        $signals = [];
        $totalScore = 0.0;

        foreach (self::AUTHENTICITY_SIGNALS as $signal => $weight) {
            $detected = $this->checkSignal($signal, $data);
            $signals[$signal] = [
                'detected' => $detected,
                'weight' => $weight,
                'contribution' => $detected ? $weight : 0.0,
            ];
            if ($detected) {
                $totalScore += $weight;
            }
        }

        $confidence = $this->calculateConfidence($totalScore, count($signals));
        $recommendation = $this->getRecommendation($totalScore);

        return [
            'score' => round($totalScore, 3),
            'confidence' => $confidence,
            'signals' => $signals,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Verify a batch of events for authenticity.
     */
    public function verifyBatch(array $events): array
    {
        $results = [];
        foreach ($events as $event) {
            $results[] = $this->verify($event);
        }

        $avgScore = array_sum(array_column($results, 'score')) / max(count($results), 1);

        return [
            'count' => count($results),
            'average_score' => round($avgScore, 3),
            'results' => $results,
        ];
    }

    private function checkSignal(string $signal, array $data): bool
    {
        return match ($signal) {
            'has_mouse_movement' => !empty($data['has_mouse_movement']) || ($data['event_count'] ?? 0) > 5,
            'has_scroll_events' => !empty($data['has_scroll_events']) || ($data['scroll_depth'] ?? 0) > 0,
            'has_focus_events' => !empty($data['has_focus_events']),
            'realistic_duration' => $this->hasRealisticDuration($data),
            'natural_navigation' => $this->hasNaturalNavigation($data),
            'human_like_timing' => $this->hasHumanLikeTiming($data),
            'consistent_device' => $this->hasConsistentDevice($data),
            default => false,
        };
    }

    private function hasRealisticDuration(array $data): bool
    {
        $duration = $data['duration'] ?? 0;
        $pageViews = $data['page_views'] ?? 1;

        // Too fast = bot, too long = abandoned
        if ($duration < 2 && $pageViews > 1) {
            return false;
        }

        // Realistic: at least 3 seconds per page view on average
        $avgPerView = $pageViews > 0 ? $duration / $pageViews : 0;

        return $avgPerView >= 3.0 && $avgPerView <= 300;
    }

    private function hasNaturalNavigation(array $data): bool
    {
        $paths = $data['visited_paths'] ?? [];

        if (count($paths) <= 1) {
            return true;
        }

        // Check for natural path patterns (not all different, not all same)
        $uniquePaths = count(array_unique($paths));
        $totalPaths = count($paths);

        // Mix of unique and repeated paths is natural
        $uniqueRatio = $totalPaths > 0 ? $uniquePaths / $totalPaths : 0;

        return $uniqueRatio > 0.2 && $uniqueRatio < 0.95;
    }

    private function hasHumanLikeTiming(array $data): bool
    {
        $timestamps = $data['event_timestamps'] ?? [];

        if (count($timestamps) < 3) {
            return true;
        }

        // Check for too-regular intervals (bot behavior)
        $intervals = [];
        for ($i = 1; $i < count($timestamps); $i++) {
            $intervals[] = $timestamps[$i] - $timestamps[$i - 1];
        }

        // Human timing has variance; bot timing is regular
        $mean = array_sum($intervals) / count($intervals);
        $variance = 0;
        foreach ($intervals as $interval) {
            $variance += ($interval - $mean) ** 2;
        }
        $variance /= count($intervals);

        // High variance = human, low variance = bot
        return $variance > 100;
    }

    private function hasConsistentDevice(array $data): bool
    {
        $deviceChanges = $data['device_changes'] ?? 0;

        // Device shouldn't change during a session
        return $deviceChanges === 0;
    }

    private function calculateConfidence(float $score, int $totalSignals): string
    {
        $detectedRatio = $score / array_sum(array_values(self::AUTHENTICITY_SIGNALS));

        if ($detectedRatio >= 0.8) {
            return 'high';
        }
        if ($detectedRatio >= 0.5) {
            return 'medium';
        }

        return 'low';
    }

    private function getRecommendation(float $score): string
    {
        if ($score >= 0.7) {
            return 'Data appears authentic. Include in analytics reports.';
        }
        if ($score >= 0.4) {
            return 'Data may be partially authentic. Review before including in reports.';
        }

        return 'Data appears suspicious. Exclude from analytics reports or flag for manual review.';
    }
}
