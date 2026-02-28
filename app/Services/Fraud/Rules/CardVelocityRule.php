<?php

namespace App\Services\Fraud\Rules;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Contracts\FraudRuleInterface;
use App\Services\Fraud\Results\FraudRuleResult;
use App\Services\Fraud\Support\RedisVelocityStore;

/**
 * Rolling-window card (fingerprint) velocity. Uses tokenized fingerprint only; no PAN.
 * Redis only; fail-open via RedisVelocityStore.
 */
class CardVelocityRule implements FraudRuleInterface
{
    public function __construct(
        private readonly RedisVelocityStore $redisStore,
        private readonly int $windowSeconds,
        private readonly int $maxTransactionsInWindow,
        private readonly float $weight
    ) {
    }

    public function evaluate(FraudContext $context): ?FraudRuleResult
    {
        if (empty($context->cardFingerprint)) {
            return null;
        }

        $key = sprintf('fraud:card:%s:txns', $context->cardFingerprint);
        $count = $this->redisStore->addAndCountInWindow($key, $this->windowSeconds);

        if ($count <= $this->maxTransactionsInWindow) {
            return null;
        }

        return new FraudRuleResult(
            ruleName: 'CardVelocityRule',
            weight: $this->weight,
            reason: sprintf(
                'High card velocity: %d transactions for same card fingerprint within %d seconds (threshold: %d).',
                $count,
                $this->windowSeconds,
                $this->maxTransactionsInWindow
            ),
            metadata: [
                'window_seconds' => $this->windowSeconds,
                'transaction_cnt' => $count,
                'threshold' => $this->maxTransactionsInWindow,
            ]
        );
    }
}
