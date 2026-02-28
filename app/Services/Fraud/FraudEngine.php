<?php

namespace App\Services\Fraud;

use App\Services\Fraud\Context\FraudContext;
use App\Services\Fraud\Contracts\FraudRuleInterface;
use App\Services\Fraud\Results\FraudResult;
use App\Services\Fraud\Results\FraudRuleResult;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates fraud rule evaluation and decisioning.
 * Per-rule failures are fail-open: log and continue so one bad rule does not block the flow.
 */
class FraudEngine
{
    /**
     * @param FraudRuleInterface[] $rules
     */
    public function __construct(
        private readonly DecisionService $decisionService,
        private readonly array $rules
    ) {
        foreach ($this->rules as $rule) {
            if (! $rule instanceof FraudRuleInterface) {
                throw new \InvalidArgumentException('All rules must implement FraudRuleInterface');
            }
        }
    }

    public function evaluate(FraudContext $context): FraudResult
    {
        $start = microtime(true);
        $triggered = [];

        foreach ($this->rules as $rule) {
            try {
                $result = $rule->evaluate($context);
                if ($result !== null) {
                    $triggered[] = $result;
                }
            } catch (\Throwable $e) {
                Log::channel('single')->warning('Fraud rule failed (fail-open)', [
                    'rule' => $rule::class,
                    'transaction_id' => $context->transactionId,
                    'message' => $e->getMessage(),
                ]);
                // Fail-open: do not add to triggered, continue with other rules
            }
        }

        $riskScore = array_sum(array_map(fn (FraudRuleResult $r) => $r->weight, $triggered));
        $decision = $this->decisionService->decide($riskScore, $context);
        $executionTimeMs = (microtime(true) - $start) * 1000;

        return new FraudResult(
            riskScore: $riskScore,
            decision: $decision,
            triggeredRules: $triggered,
            executionTimeMs: $executionTimeMs,
            context: $context
        );
    }
}
