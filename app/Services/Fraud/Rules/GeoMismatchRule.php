<?php

namespace App\Services\Fraud\Rules;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Contracts\FraudRuleInterface;
use App\Services\Fraud\Results\FraudRuleResult;

/**
 * Geo mismatches (IP vs billing/card/merchant) and high-risk country list.
 * No Redis or DB; uses context metadata only.
 */
class GeoMismatchRule implements FraudRuleInterface
{
    /**
     * @param string[] $highRiskCountries ISO country codes
     */
    public function __construct(
        private readonly float $weightMismatch,
        private readonly float $weightHighRisk,
        private readonly array $highRiskCountries = []
    ) {
    }

    public function evaluate(FraudContext $context): ?FraudRuleResult
    {
        $ipCountry = $context->countryCode;
        $billingCountry = $context->metadata['billing_country'] ?? null;
        $cardCountry = $context->metadata['card_country'] ?? null;
        $merchantCountry = $context->metadata['merchant_country'] ?? null;

        $mismatches = [];
        if ($ipCountry && $billingCountry && $ipCountry !== $billingCountry) {
            $mismatches[] = sprintf('IP country (%s) differs from billing country (%s).', $ipCountry, $billingCountry);
        }
        if ($ipCountry && $cardCountry && $ipCountry !== $cardCountry) {
            $mismatches[] = sprintf('IP country (%s) differs from card country (%s).', $ipCountry, $cardCountry);
        }
        if ($billingCountry && $merchantCountry && $billingCountry !== $merchantCountry) {
            $mismatches[] = sprintf('Billing country (%s) differs from merchant country (%s).', $billingCountry, $merchantCountry);
        }

        if (empty($mismatches)) {
            return null;
        }

        $countriesToCheck = array_filter([$ipCountry, $billingCountry, $cardCountry]);
        $isHighRisk = false;
        foreach ($countriesToCheck as $c) {
            if (in_array($c, $this->highRiskCountries, true)) {
                $isHighRisk = true;
                break;
            }
        }

        $weight = $this->weightMismatch + ($isHighRisk ? $this->weightHighRisk : 0.0);
        $reason = implode(' ', $mismatches);
        if ($isHighRisk) {
            $reason .= ' One or more countries is in the configured high-risk list.';
        }

        return new FraudRuleResult(
            ruleName: 'GeoMismatchRule',
            weight: $weight,
            reason: $reason,
            metadata: [
                'ip_country' => $ipCountry,
                'billing_country' => $billingCountry,
                'card_country' => $cardCountry,
                'merchant_country' => $merchantCountry,
                'is_high_risk' => $isHighRisk,
                'mismatch_count' => count($mismatches),
            ]
        );
    }
}
