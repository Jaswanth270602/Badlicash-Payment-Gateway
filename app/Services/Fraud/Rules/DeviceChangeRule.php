<?php

namespace App\Services\Fraud\Rules;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Contracts\FraudRuleInterface;
use App\Services\Fraud\Results\FraudRuleResult;
use App\Services\Fraud\Support\RedisVelocityStore;

/**
 * New or changed device for same customer/merchant. Redis-backed; fail-open.
 */
class DeviceChangeRule implements FraudRuleInterface
{
    public function __construct(
        private readonly RedisVelocityStore $redisStore,
        private readonly float $weightNewDevice,
        private readonly float $weightDeviceChange,
        private readonly int $ttlSeconds
    ) {
    }

    public function evaluate(FraudContext $context): ?FraudRuleResult
    {
        if ($context->merchantId === null || $context->customerId === null || empty($context->deviceId)) {
            return null;
        }

        $key = sprintf('fraud:device:last:%d:%s', $context->merchantId, $context->customerId);
        $previousDeviceId = $this->redisStore->getAndSet($key, $context->deviceId, $this->ttlSeconds);

        if ($previousDeviceId === null) {
            return new FraudRuleResult(
                ruleName: 'DeviceChangeRule',
                weight: $this->weightNewDevice,
                reason: 'New device observed for this customer and merchant.',
                metadata: [
                    'merchant_id' => $context->merchantId,
                    'change_type' => 'new',
                ]
            );
        }

        if ($previousDeviceId === $context->deviceId) {
            return null;
        }

        return new FraudRuleResult(
            ruleName: 'DeviceChangeRule',
            weight: $this->weightDeviceChange,
            reason: 'Device changed for this customer and merchant (different from last seen device).',
            metadata: [
                'merchant_id' => $context->merchantId,
                'change_type' => 'change',
            ]
        );
    }
}
