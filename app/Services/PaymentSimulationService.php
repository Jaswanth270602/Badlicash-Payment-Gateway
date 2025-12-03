<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\PaymentLink;
use App\Events\PaymentSuccess;
use App\Events\PaymentFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentSimulationService
{
    /**
     * Process payment (simulation for test mode or real for live mode).
     */
    public function processPayment(array $paymentData): array
    {
        return DB::transaction(function () use ($paymentData) {
            try {
                // Create or find order
                $order = $this->createOrder($paymentData);

                // Create transaction
                $transaction = $this->createTransaction($order, $paymentData);

                // Simulate payment processing
                $paymentResult = $this->simulatePaymentGateway($paymentData);

                // Update transaction and order based on result
                if ($paymentResult['success']) {
                    $transaction->update([
                        'status' => 'success',
                        'gateway_response' => $paymentResult,
                        'gateway_txn_id' => $paymentResult['gateway_txn_id'] ?? null,
                        'captured_at' => now(),
                    ]);

                    $order->update(['status' => 'completed']);

                    // Update payment link if used
                    if (isset($paymentData['payment_link_id'])) {
                        $paymentLink = PaymentLink::find($paymentData['payment_link_id']);
                        if ($paymentLink) {
                            $paymentLink->increment('usage_count');
                            $paymentLink->update(['status' => 'paid']);
                        }
                    }

                    event(new PaymentSuccess($transaction));

                    return [
                        'success' => true,
                        'message' => 'Payment successful',
                        'order_id' => $order->order_id,
                        'transaction_id' => $transaction->txn_id,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'redirect_url' => $this->getSuccessUrl($paymentData),
                    ];
                } else {
                    $transaction->update([
                        'status' => 'failed',
                        'gateway_response' => $paymentResult,
                    ]);

                    $order->update(['status' => 'failed']);

                    event(new PaymentFailed($transaction));

                    return [
                        'success' => false,
                        'message' => $paymentResult['message'] ?? 'Payment failed',
                        'order_id' => $order->order_id,
                        'transaction_id' => $transaction->txn_id,
                        'error_code' => $paymentResult['error_code'] ?? 'PAYMENT_FAILED',
                        'redirect_url' => $this->getFailureUrl($paymentData),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Payment simulation error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Create order from payment data.
     */
    protected function createOrder(array $paymentData): Order
    {
        return Order::create([
            'merchant_id' => $paymentData['merchant_id'],
            'order_id' => Order::generateOrderId(),
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
            'customer_details' => $paymentData['customer_details'] ?? null,
            'status' => 'created',
            'description' => $paymentData['description'] ?? null,
            'test_mode' => $paymentData['test_mode'] ?? false,
            'payment_link_id' => $paymentData['payment_link_id'] ?? null,
            'expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Create transaction from order and payment data.
     */
    protected function createTransaction(Order $order, array $paymentData): Transaction
    {
        $feeAmount = $this->calculateFee($order->amount);
        $netAmount = $order->amount - $feeAmount;

        return Transaction::create([
            'order_id' => $order->id,
            'merchant_id' => $order->merchant_id,
            'txn_id' => Transaction::generateTxnId(),
            'payment_method' => $paymentData['payment_method'],
            'amount' => $order->amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'currency' => $order->currency,
            'status' => 'initiated',
            'payment_details' => $this->sanitizePaymentDetails($paymentData['payment_details']),
            'test_mode' => $order->test_mode,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Simulate payment gateway processing.
     * In test mode, uses test card numbers to determine success/failure.
     * In live mode, would integrate with real payment gateway.
     */
    protected function simulatePaymentGateway(array $paymentData): array
    {
        $paymentMethod = $paymentData['payment_method'];
        $paymentDetails = $paymentData['payment_details'];
        $testMode = $paymentData['test_mode'] ?? false;

        // In test mode, simulate based on test data
        if ($testMode) {
            return $this->simulateTestPayment($paymentMethod, $paymentDetails);
        }

        // In live mode, you would integrate with real payment gateway here
        // For now, we'll treat it as test mode
        return $this->simulateTestPayment($paymentMethod, $paymentDetails);
    }

    /**
     * Simulate test payment based on test card numbers and UPI IDs.
     */
    protected function simulateTestPayment(string $paymentMethod, array $paymentDetails): array
    {
        if ($paymentMethod === 'card') {
            $cardNumber = str_replace(' ', '', $paymentDetails['card_number'] ?? '');

            // Test card numbers
            $successCards = ['4111111111111111', '5555555555554444'];
            $failureCards = ['4000000000000002', '4000000000009995'];

            if (in_array($cardNumber, $successCards)) {
                return [
                    'success' => true,
                    'gateway_txn_id' => 'TEST_' . strtoupper(uniqid()),
                    'message' => 'Payment successful',
                    'payment_method' => 'card',
                    'card_last4' => substr($cardNumber, -4),
                ];
            }

            if (in_array($cardNumber, $failureCards)) {
                return [
                    'success' => false,
                    'message' => $cardNumber === '4000000000009995' 
                        ? 'Insufficient funds' 
                        : 'Payment declined',
                    'error_code' => $cardNumber === '4000000000009995' 
                        ? 'INSUFFICIENT_FUNDS' 
                        : 'PAYMENT_DECLINED',
                ];
            }

            // Unknown card - default to success in test mode
            return [
                'success' => true,
                'gateway_txn_id' => 'TEST_' . strtoupper(uniqid()),
                'message' => 'Payment successful',
                'payment_method' => 'card',
                'card_last4' => substr($cardNumber, -4),
            ];
        }

        if ($paymentMethod === 'upi') {
            $upiId = $paymentDetails['upi_id'] ?? '';

            // Test UPI IDs
            if ($upiId === 'success@upi') {
                return [
                    'success' => true,
                    'gateway_txn_id' => 'UPI_' . strtoupper(uniqid()),
                    'message' => 'Payment successful',
                    'payment_method' => 'upi',
                    'upi_id' => $upiId,
                ];
            }

            if ($upiId === 'failure@upi') {
                return [
                    'success' => false,
                    'message' => 'UPI payment failed',
                    'error_code' => 'UPI_FAILED',
                ];
            }

            // Default to success
            return [
                'success' => true,
                'gateway_txn_id' => 'UPI_' . strtoupper(uniqid()),
                'message' => 'Payment successful',
                'payment_method' => 'upi',
            ];
        }

        // For netbanking and wallet, default to success
        return [
            'success' => true,
            'gateway_txn_id' => strtoupper($paymentMethod) . '_' . strtoupper(uniqid()),
            'message' => 'Payment successful',
            'payment_method' => $paymentMethod,
        ];
    }

    /**
     * Calculate fee for transaction.
     */
    protected function calculateFee(float $amount): float
    {
        // Default 2.5% fee
        return round($amount * 0.025, 2);
    }

    /**
     * Sanitize payment details to remove sensitive information.
     */
    protected function sanitizePaymentDetails(array $paymentDetails): array
    {
        $sanitized = $paymentDetails;

        // Remove sensitive fields
        unset($sanitized['card_number'], $sanitized['cvv'], $sanitized['pin']);

        // Keep only last 4 digits if card number was provided
        if (isset($paymentDetails['card_number'])) {
            $cardNumber = str_replace(' ', '', $paymentDetails['card_number']);
            $sanitized['last4'] = substr($cardNumber, -4);
        }

        return $sanitized;
    }

    /**
     * Get success redirect URL.
     */
    protected function getSuccessUrl(array $paymentData): string
    {
        if (isset($paymentData['payment_link_id'])) {
            $paymentLink = PaymentLink::find($paymentData['payment_link_id']);
            if ($paymentLink) {
                return route('payment.success', ['token' => $paymentLink->link_token]);
            }
        }

        return $paymentData['return_url'] ?? route('dashboard');
    }

    /**
     * Get failure redirect URL.
     */
    protected function getFailureUrl(array $paymentData): string
    {
        if (isset($paymentData['payment_link_id'])) {
            $paymentLink = PaymentLink::find($paymentData['payment_link_id']);
            if ($paymentLink) {
                return route('payment.failed', ['token' => $paymentLink->link_token]);
            }
        }

        return $paymentData['cancel_url'] ?? route('dashboard');
    }
}
