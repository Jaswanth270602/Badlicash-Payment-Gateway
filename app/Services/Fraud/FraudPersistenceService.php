<?php

namespace App\Services\Fraud;

use App\Models\FraudEvent;
use App\Models\FraudTransaction;
use App\Services\Fraud\Results\FraudResult;
use Illuminate\Support\Facades\Log;

/**
 * Persists fraud evaluation results for audit and explainability.
 * Writes one fraud_transactions row and one fraud_events row per triggered rule.
 */
class FraudPersistenceService
{
    public function persist(FraudResult $result): FraudTransaction
    {
        $context = $result->context;

        $fraudTxn = FraudTransaction::create([
            'transaction_id' => $context->transactionId,
            'merchant_id' => $context->merchantId,
            'risk_score' => $result->riskScore,
            'decision' => $result->decision,
            'triggered_rules' => array_map(fn ($r) => $r->toArray(), $result->triggeredRules),
            'execution_time_ms' => (int) round($result->executionTimeMs),
        ]);

        foreach ($result->triggeredRules as $ruleResult) {
            FraudEvent::create([
                'fraud_transaction_id' => $fraudTxn->id,
                'rule_name' => $ruleResult->ruleName,
                'weight' => $ruleResult->weight,
                'reason' => mb_substr($ruleResult->reason, 0, 500),
                'metadata' => $ruleResult->metadata ?: null,
            ]);
        }

        if (count($result->triggeredRules) > 0) {
            Log::channel('single')->info('Fraud evaluation persisted', [
                'fraud_transaction_id' => $fraudTxn->id,
                'transaction_id' => $context->transactionId,
                'merchant_id' => $context->merchantId,
                'decision' => $result->decision,
                'risk_score' => $result->riskScore,
                'triggered_rules_count' => count($result->triggeredRules),
            ]);
        }

        return $fraudTxn;
    }
}
