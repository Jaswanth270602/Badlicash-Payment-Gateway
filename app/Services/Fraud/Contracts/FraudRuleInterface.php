<?php

namespace App\Services\Fraud\Contracts;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Results\FraudRuleResult;

interface FraudRuleInterface
{
    /**
     * Evaluate this rule against the given context.
     * Returns a FraudRuleResult if the rule is triggered, or null if not.
     * No DB queries; use Redis or in-memory logic only (PCI-safe, low latency).
     */
    public function evaluate(FraudContext $context): ?FraudRuleResult;
}
