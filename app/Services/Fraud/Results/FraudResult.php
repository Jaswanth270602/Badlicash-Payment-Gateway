<?php

namespace App\Services\Fraud\Results;

use App\Services\Fraud\Context\FraudContext;

/**
 * Final decision result from the fraud engine (in-memory DTO).
 */
class FraudResult
{
    /**
     * @param FraudRuleResult[] $triggeredRules
     */
    public function __construct(
        public readonly float $riskScore,
        public readonly string $decision,
        public readonly array $triggeredRules,
        public readonly float $executionTimeMs,
        public readonly FraudContext $context
    ) {
    }

    public function toArray(): array
    {
        return [
            'risk_score' => $this->riskScore,
            'decision' => $this->decision,
            'triggered_rules' => array_map(fn (FraudRuleResult $r) => $r->toArray(), $this->triggeredRules),
            'execution_time_ms' => $this->executionTimeMs,
        ];
    }
}
