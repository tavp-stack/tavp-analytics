<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

use Tavp\Analytics\Models\FraudEvent;

/**
 * Central fraud detection engine.
 * Coordinates multiple detection rules and computes a composite fraud score.
 *
 * Fraud score: 0.0 (clean) → 1.0 (definite fraud)
 *
 * Detection layers:
 * 1. Bot detection — known bot signatures
 * 2. Velocity checks — too many requests in short time
 * 3. Anomaly detection — statistical outliers
 * 4. Pattern detection — known fraud patterns
 * 5. Device fingerprint — suspicious device characteristics
 * 6. Geographic — impossible travel, VPN/proxy detection
 */
class FraudDetector
{
    private array $rules = [];
    private array $config;
    private array $ipCache = [];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'bot_detection' => true,
            'velocity_limit' => 100,
            'velocity_window' => 60,
            'anomaly_threshold' => 3.0,
            'block_suspicious' => false,
            'min_score_to_flag' => 0.5,
            'min_score_to_block' => 0.8,
        ], $config);

        $this->registerDefaultRules();
    }

    /**
     * Analyze a request and return a fraud assessment.
     *
     * @return array{score: float, is_suspicious: bool, should_block: bool, rules_triggered: array, details: array}
     */
    public function analyze(array $data): array
    {
        $scores = [];
        $rulesTriggered = [];
        $details = [];

        foreach ($this->rules as $rule) {
            $result = $rule->evaluate($data, $this->config);

            if ($result['score'] > 0) {
                $scores[] = $result['score'];
                $rulesTriggered[] = $rule->getName();
                $details[$rule->getName()] = $result;
            }
        }

        // Compute weighted average score
        $compositeScore = empty($scores) ? 0.0 : min(1.0, max($scores));

        // Boost score if multiple rules triggered
        if (count($rulesTriggered) >= 3) {
            $compositeScore = min(1.0, $compositeScore * 1.5);
        }

        $isSuspicious = $compositeScore >= $this->config['min_score_to_flag'];
        $shouldBlock = $compositeScore >= $this->config['min_score_to_block'];

        // Log fraud event if suspicious
        if ($isSuspicious) {
            $this->logFraudEvent($data, $compositeScore, $rulesTriggered, $details);
        }

        return [
            'score' => $compositeScore,
            'is_suspicious' => $isSuspicious,
            'should_block' => $shouldBlock,
            'rules_triggered' => $rulesTriggered,
            'details' => $details,
        ];
    }

    /**
     * Register a custom fraud detection rule.
     */
    public function addRule(FraudRule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Get all registered rules.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    private function registerDefaultRules(): void
    {
        if ($this->config['bot_detection']) {
            $this->addRule(new BotDetectionRule());
        }

        $this->addRule(new VelocityRule(
            $this->config['velocity_limit'],
            $this->config['velocity_window']
        ));

        $this->addRule(new AnomalyDetectionRule(
            $this->config['anomaly_threshold']
        ));

        $this->addRule(new DeviceFingerprintRule());
        $this->addRule(new GeographicRule());
        $this->addRule(new PatternDetectionRule());
        $this->addRule(new ClickFraudRule());
    }

    private function logFraudEvent(array $data, float $score, array $rules, array $details): void
    {
        $event = new FraudEvent();
        $event->fill([
            'session_id' => $data['session_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'ip_address' => $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''),
            'event_type' => 'auto_detection',
            'rule_name' => implode(', ', $rules),
            'score' => $score,
            'details' => $details,
            'action_taken' => $score >= $this->config['min_score_to_block'] ? 'blocked' : 'flagged',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $event->save();
    }
}
