<?php

namespace App\Services\Fraud\Context;

/**
 * Immutable value object describing the transaction in a PCI-safe way.
 *
 * DO NOT store this directly; it is used in-memory only.
 * Do not put full PANs or other raw card data here – use tokens/fingerprints.
 */
class FraudContext
{
    public function __construct(
        public readonly string $transactionId,
        public readonly ?int $merchantId,
        public readonly string $paymentMethod,
        public readonly int|float $amount,
        public readonly string $currency,
        public readonly ?string $customerId,
        public readonly ?string $ipAddress,
        public readonly ?string $deviceId,
        public readonly ?string $countryCode,
        public readonly ?string $cardFingerprint,
        public readonly ?string $bin,
        public readonly ?array $metadata = []
    ) {
    }

    /**
     * Clone with overrides (e.g. for testing).
     */
    public function with(array $overrides): self
    {
        $data = [
            'transactionId' => $this->transactionId,
            'merchantId' => $this->merchantId,
            'paymentMethod' => $this->paymentMethod,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customerId' => $this->customerId,
            'ipAddress' => $this->ipAddress,
            'deviceId' => $this->deviceId,
            'countryCode' => $this->countryCode,
            'cardFingerprint' => $this->cardFingerprint,
            'bin' => $this->bin,
            'metadata' => $this->metadata,
        ];
        $data = array_replace($data, $overrides);

        return new self(
            $data['transactionId'],
            $data['merchantId'],
            $data['paymentMethod'],
            $data['amount'],
            $data['currency'],
            $data['customerId'],
            $data['ipAddress'],
            $data['deviceId'],
            $data['countryCode'],
            $data['cardFingerprint'],
            $data['bin'],
            $data['metadata'],
        );
    }
}
