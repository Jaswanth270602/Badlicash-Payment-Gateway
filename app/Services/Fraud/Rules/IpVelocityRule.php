<?php

namespace App\Services\Fraud\Rules;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Contracts\FraudRuleInterface;
use App\Services\Fraud\Results\FraudRuleResult;
use App\Services\Fraud\Support\RedisVelocityStore;

/**
 * Rolling-window IP velocity: too many transactions from same IP in window.
 * Uses Redis only (no DB). Fail-open via RedisVelocityStore.
 */
class IpVelocityRule implements FraudRuleInterface
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
        if (empty($context->ipAddress)) {
            return null;
        }

        $key = sprintf('fraud:ip:%s:txns', $context->ipAddress);
        $count = $this->redisStore->addAndCountInWindow($key, $this->windowSeconds);

        if ($count <= $this->maxTransactionsInWindow) {
            return null;
        }

        return new FraudRuleResult(
            ruleName: 'IpVelocityRule',
            weight: $this->weight,
            reason: sprintf(
                'High IP velocity: %d transactions from same IP within %d seconds (threshold: %d).',
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
