<?php

declare(strict_types=1);

namespace Tavp\Analytics\Fraud;

/**
 * Contract for fraud detection rules.
 * Each rule evaluates a request and returns a score + explanation.
 */
interface FraudRule
{
    /**
     * Evaluate the data against this rule.
     *
     * @return array{score: float, reason: string, data: array}
     */
    public function evaluate(array $data, array $config): array;

    /**
     * Get the rule name.
     */
    public function getName(): string;
}
