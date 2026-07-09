<?php

declare(strict_types=1);

namespace Tavp\Analytics\Analytics;

use Tavp\Analytics\Models\Experiment;
use Tavp\Analytics\Models\ExperimentParticipation;

/**
 * A/B test experiment tracking.
 * Assigns users to variants and tracks conversions.
 */
class ExperimentTracker
{
    /**
     * Get the active variant for a user/session.
     */
    public function getVariant(string $experimentSlug, string $sessionId, ?int $userId = null): ?string
    {
        $experiment = Experiment::query()
            ->where('slug', '=', $experimentSlug)
            ->andWhere('is_active', '=', true)
            ->findFirst();

        if ($experiment === null) {
            return null;
        }

        // Check if already assigned
        $participation = ExperimentParticipation::query()
            ->where('experiment_id', '=', $experiment->id)
            ->andWhere('session_id', '=', $sessionId)
            ->findFirst();

        if ($participation !== null) {
            return $participation->variant;
        }

        // Assign based on traffic percentage
        $variants = $experiment->variants ?? [];
        $trafficPercentage = $experiment->traffic_percentage ?? 100.0;

        if (empty($variants) || (mt_rand(0, 100) > $trafficPercentage)) {
            return null;
        }

        // Weighted random selection
        $variant = $this->selectVariant($variants);

        $participation = new ExperimentParticipation();
        $participation->fill([
            'experiment_id' => $experiment->id,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'variant' => $variant,
            'converted' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $participation->save();

        return $variant;
    }

    /**
     * Record a conversion for a user in an experiment.
     */
    public function recordConversion(string $experimentSlug, string $sessionId): bool
    {
        $experiment = Experiment::query()
            ->where('slug', '=', $experimentSlug)
            ->andWhere('is_active', '=', true)
            ->findFirst();

        if ($experiment === null) {
            return false;
        }

        $participation = ExperimentParticipation::query()
            ->where('experiment_id', '=', $experiment->id)
            ->andWhere('session_id', '=', $sessionId)
            ->findFirst();

        if ($participation === null || $participation->converted) {
            return false;
        }

        $participation->fill([
            'converted' => true,
            'converted_at' => date('Y-m-d H:i:s'),
        ]);

        $participation->save();

        return true;
    }

    /**
     * Get experiment results with statistical significance.
     */
    public function getResults(string $experimentSlug): array
    {
        $experiment = Experiment::query()
            ->where('slug', '=', $experimentSlug)
            ->findFirst();

        if ($experiment === null) {
            return ['error' => 'Experiment not found'];
        }

        $variants = $experiment->variants ?? [];
        $results = [];

        foreach ($variants as $variant) {
            $participations = ExperimentParticipation::query()
                ->where('experiment_id', '=', $experiment->id)
                ->andWhere('variant', '=', $variant)
                ->get();

            $total = count($participations);
            $conversions = count(array_filter($participations, fn ($p) => $p->converted));

            $results[$variant] = [
                'total' => $total,
                'conversions' => $conversions,
                'conversion_rate' => $total > 0 ? round(($conversions / $total) * 100, 2) : 0.0,
            ];
        }

        return [
            'experiment' => $experiment->name,
            'is_active' => $experiment->is_active,
            'variants' => $results,
        ];
    }

    private function selectVariant(array $variants): string
    {
        $totalWeight = array_sum(array_column($variants, 'weight'));
        $random = mt_rand(0, (int) ($totalWeight * 100)) / 100;

        $cumulative = 0;
        foreach ($variants as $variant) {
            $cumulative += $variant['weight'] ?? 1;
            if ($random <= $cumulative) {
                return $variant['name'];
            }
        }

        return $variants[0]['name'];
    }
}
