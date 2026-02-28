<?php

namespace App\Services\Fraud\Rules;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Contracts\FraudRuleInterface;
use App\Services\Fraud\Results\FraudRuleResult;

/**
 * Threshold-based amount checks. No Redis or DB; in-memory only.
 */
class AmountAnomalyRule implements FraudRuleInterface
{
    public function __construct(
        private readonly float $highAmountThreshold,
        private readonly float $veryHighAmountThreshold,
        private readonly float $weightHigh,
        private readonly float $weightVeryHigh
    ) {
    }

    public function evaluate(FraudContext $context): ?FraudRuleResult
    {
        $amount = (float) $context->amount;

        if ($amount >= $this->veryHighAmountThreshold) {
            return new FraudRuleResult(
                ruleName: 'AmountAnomalyRule',
                weight: $this->weightVeryHigh,
                reason: sprintf(
                    'Very high transaction amount: %.2f %s (>= %.2f).',
                    $amount,
                    $context->currency,
                    $this->veryHighAmountThreshold
                ),
                metadata: [
                    'amount' => $amount,
                    'currency' => $context->currency,
                    'severity' => 'very_high',
                ]
            );
        }

        if ($amount >= $this->highAmountThreshold) {
            return new FraudRuleResult(
                ruleName: 'AmountAnomalyRule',
                weight: $this->weightHigh,
                reason: sprintf(
                    'High transaction amount: %.2f %s (>= %.2f).',
                    $amount,
                    $context->currency,
                    $this->highAmountThreshold
                ),
                metadata: [
                    'amount' => $amount,
                    'currency' => $context->currency,
                    'severity' => 'high',
                ]
            );
        }

        return null;
    }
}
