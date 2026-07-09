<?php

declare(strict_types=1);

namespace Tavp\Analytics\Analytics;

use Tavp\Analytics\Models\Funnel;
use Tavp\Analytics\Models\FunnelEvent;

/**
 * Funnel analysis — tracks user progress through multi-step flows.
 */
class FunnelAnalyzer
{
    /**
     * Get conversion rates for a funnel.
     */
    public function analyze(string $funnelSlug): array
    {
        $funnel = Funnel::query()
            ->where('slug', '=', $funnelSlug)
            ->andWhere('is_active', '=', true)
            ->findFirst();

        if ($funnel === null) {
            return ['error' => 'Funnel not found'];
        }

        $steps = $funnel->steps ?? [];
        $results = [];

        foreach ($steps as $index => $step) {
            $count = FunnelEvent::query()
                ->where('funnel_id', '=', $funnel->id)
                ->andWhere('step_index', '=', $index)
                ->count();

            $results[] = [
                'step' => $step['name'] ?? "Step {$index}",
                'count' => $count,
                'conversion_rate' => 0.0,
            ];
        }

        // Calculate conversion rates
        if (!empty($results)) {
            $firstCount = $results[0]['count'];
            foreach ($results as &$step) {
                $step['conversion_rate'] = $firstCount > 0
                    ? round(($step['count'] / $firstCount) * 100, 2)
                    : 0.0;
            }
        }

        return [
            'funnel' => $funnel->name,
            'steps' => $results,
            'overall_conversion' => end($results)['conversion_rate'] ?? 0.0,
        ];
    }

    /**
     * Record a funnel step completion.
     */
    public function recordStep(string $funnelSlug, int $stepIndex, array $data = []): bool
    {
        $funnel = Funnel::query()
            ->where('slug', '=', $funnelSlug)
            ->andWhere('is_active', '=', true)
            ->findFirst();

        if ($funnel === null) {
            return false;
        }

        $steps = $funnel->steps ?? [];
        if (!isset($steps[$stepIndex])) {
            return false;
        }

        $event = new FunnelEvent();
        $event->fill([
            'funnel_id' => $funnel->id,
            'session_id' => $data['session_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'step_index' => $stepIndex,
            'step_name' => $steps[$stepIndex]['name'] ?? "Step {$stepIndex}",
            'metadata' => $data['metadata'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $event->save();

        return true;
    }
}
