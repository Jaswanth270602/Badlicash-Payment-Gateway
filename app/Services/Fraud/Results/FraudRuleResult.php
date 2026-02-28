<?php

namespace App\Services\Fraud\Results;

/**
 * Result for a single triggered rule (in-memory DTO, not persisted directly).
 */
class FraudRuleResult
{
    public function __construct(
        public readonly string $ruleName,
        public readonly float $weight,
        public readonly string $reason,
        public readonly array $metadata = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'rule_name' => $this->ruleName,
            'weight' => $this->weight,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
        ];
    }
}
