<?php

namespace App\Services\Fraud;

use App\Services\Fraud\Context\FraudContext;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

/**
 * Maps a numeric risk score to a decision (allow | review | block).
 *
 * Thresholds and overrides are read from config/fraud.php.
 * Resolution order (later overrides earlier): global → payment_method → merchant → merchant+payment_method.
 */
class DecisionService
{
    public function __construct(
        private readonly ConfigRepository $config
    ) {
    }

    /**
     * Get effective allow/block thresholds for the given context.
     * Used by decide() and testable in isolation.
     *
     * @return array{allow: float, block: float}
     */
    public function getThresholdsForContext(FraudContext $context): array
    {
        $base = [
            'allow' => (float) $this->config->get('fraud.thresholds.allow', 20.0),
            'block' => (float) $this->config->get('fraud.thresholds.block', 70.0),
        ];

        $paymentMethod = $context->paymentMethod;
        $merchantId = $context->merchantId;

        $pmOverrides = $this->config->get('fraud.thresholds.payment_method_overrides', []);
        if ($paymentMethod !== null && isset($pmOverrides[$paymentMethod])) {
            $base = $this->mergeThresholds($base, $pmOverrides[$paymentMethod]);
        }

        $merchantOverrides = $this->config->get('fraud.thresholds.merchant_overrides', []);
        $merchantKey = $merchantId !== null ? (string) $merchantId : null;
        if ($merchantKey !== null && isset($merchantOverrides[$merchantKey])) {
            $base = $this->mergeThresholds($base, $merchantOverrides[$merchantKey]);
        }

        $mpOverrides = $this->config->get('fraud.thresholds.merchant_payment_overrides', []);
        if ($merchantKey !== null && $paymentMethod !== null && isset($mpOverrides[$merchantKey][$paymentMethod])) {
            $base = $this->mergeThresholds($base, $mpOverrides[$merchantKey][$paymentMethod]);
        }

        $this->validateThresholds($base);

        return $base;
    }

    /**
     * Decide allow, review, or block based on score and context-aware thresholds.
     */
    public function decide(float $riskScore, FraudContext $context): string
    {
        $thresholds = $this->getThresholdsForContext($context);

        if ($riskScore >= $thresholds['block']) {
            return 'block';
        }

        if ($riskScore <= $thresholds['allow']) {
            return 'allow';
        }

        return 'review';
    }

    /**
     * @param array{allow?: float, block?: float} $overlay
     * @return array{allow: float, block: float}
     */
    private function mergeThresholds(array $base, array $overlay): array
    {
        if (isset($overlay['allow'])) {
            $base['allow'] = (float) $overlay['allow'];
        }
        if (isset($overlay['block'])) {
            $base['block'] = (float) $overlay['block'];
        }
        return $base;
    }

    /**
     * @param array{allow: float, block: float} $t
     */
    private function validateThresholds(array $t): void
    {
        if ($t['allow'] >= $t['block']) {
            throw new \InvalidArgumentException(
                'Fraud thresholds invalid: allow must be < block (allow=' . $t['allow'] . ', block=' . $t['block'] . '). ' .
                'Check config/fraud.php and any merchant/payment_method overrides.'
            );
        }
    }
}
