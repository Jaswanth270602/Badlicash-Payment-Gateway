<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\PaymentLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSimulationService
{
    /**
     * Test card numbers that always succeed
     */
    private const SUCCESS_CARDS = [
        '4242424242424242', // Visa
        '5555555555554444', // Mastercard
        '378282246310005',  // Amex
        '6011111111111117', // Discover
    ];

    /**
     * Test card numbers that always fail
     */
    private const FAILURE_CARDS = [
        '4000000000000002', // Card declined
        '4000000000009995', // Insufficient funds
        '4000000000009987', // Lost card
        '4000000000009979', // Stolen card
    ];

    /**
     * Process payment simulation
     *
     * @param array $paymentData
     * @return array
     */
    public function processPayment(array $paymentData): array
    {
        try {
            \Log::info('=== PAYMENT PROCESSING STARTED ===', ['data' => $paymentData]);
            
            DB::beginTransaction();

            // Determine if payment should succeed
            $success = $this->shouldPaymentSucceed($paymentData);
            \Log::info('Payment decision made', ['success' => $success]);

            // Create order
            \Log::info('Creating order...');
            $order = $this->createOrder($paymentData, $success);
            \Log::info('Order created', ['order_id' => $order->order_id]);

            // Create transaction
            \Log::info('Creating transaction...');
            $transaction = $this->createTransaction($order, $paymentData, $success);
            \Log::info('Transaction created', ['transaction_id' => $transaction->transaction_id]);

            // Update payment link if exists
            if (isset($paymentData['payment_link_id'])) {
                \Log::info('Updating payment link...', ['payment_link_id' => $paymentData['payment_link_id']]);
                $this->updatePaymentLink($paymentData['payment_link_id'], $success);
            }

            DB::commit();
            \Log::info('=== PAYMENT PROCESSING COMPLETE ===', ['success' => $success]);

            return [
                'success' => $success,
                'order_id' => $order->order_id,
                'transaction_id' => $transaction->transaction_id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'message' => $success 
                    ? 'Payment processed successfully!' 
                    : $this->getFailureMessage($paymentData),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('=== PAYMENT PROCESSING FAILED ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment processing failed. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine if payment should succeed based on test data and random chance
     *
     * @param array $paymentData
     * @return bool
     */
    private function shouldPaymentSucceed(array $paymentData): bool
    {
        $paymentMethod = $paymentData['payment_method'] ?? 'card';

        // For card payments, check test card numbers
        if ($paymentMethod === 'card' && isset($paymentData['payment_details']['card_number'])) {
            $cardNumber = preg_replace('/\D/', '', $paymentData['payment_details']['card_number']); // Remove all non-digits
            
            \Log::info('Checking card number', [
                'original' => $paymentData['payment_details']['card_number'],
                'cleaned' => $cardNumber,
                'success_cards' => self::SUCCESS_CARDS,
                'failure_cards' => self::FAILURE_CARDS
            ]);
            
            // Check if it's a success test card
            foreach (self::SUCCESS_CARDS as $testCard) {
                if ($cardNumber === preg_replace('/\D/', '', $testCard)) {
                    \Log::info('SUCCESS CARD MATCHED!', ['card' => $cardNumber]);
                    return true;
                }
            }
            
            // Check if it's a failure test card
            foreach (self::FAILURE_CARDS as $testCard) {
                if ($cardNumber === preg_replace('/\D/', '', $testCard)) {
                    \Log::info('FAILURE CARD MATCHED!', ['card' => $cardNumber]);
                    return false;
                }
            }
            
            \Log::info('No test card match, using random', ['card' => $cardNumber]);
        }

        // For other methods or regular cards, use 70/30 random
        $random = rand(1, 100);
        $success = $random <= 70; // 70% success rate
        
        \Log::info('Random payment result', [
            'random' => $random,
            'success' => $success,
            'method' => $paymentMethod
        ]);
        
        return $success;
    }

    /**
     * Create order record
     *
     * @param array $paymentData
     * @param bool $success
     * @return Order
     */
    private function createOrder(array $paymentData, bool $success): Order
    {
        $order = Order::create([
            'merchant_id' => $paymentData['merchant_id'],
            'payment_link_id' => $paymentData['payment_link_id'] ?? null,
            'order_id' => 'ORD-' . strtoupper(Str::random(12)),
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
            'payment_method' => $paymentData['payment_method'],
            'payment_details' => $this->sanitizePaymentDetails($paymentData['payment_details'] ?? []),
            'customer_details' => $paymentData['customer_details'] ?? [],
            'status' => $success ? 'completed' : 'failed',
            'test_mode' => $paymentData['test_mode'] ?? true,
            'description' => $paymentData['description'] ?? 'Payment Link Order',
        ]);

        return $order;
    }

    /**
     * Create transaction record
     *
     * @param Order $order
     * @param array $paymentData
     * @param bool $success
     * @return Transaction
     */
    private function createTransaction(Order $order, array $paymentData, bool $success): Transaction
    {
        $txnId = 'TXN_' . strtoupper(Str::random(20));
        
        $transaction = Transaction::create([
            'merchant_id' => $order->merchant_id,
            'order_id' => $order->id,
            'txn_id' => $txnId,
            'transaction_id' => $txnId,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'payment_method' => $order->payment_method,
            'status' => $success ? 'success' : 'failed',
            'gateway' => 'simulation',
            'gateway_transaction_id' => 'SIM-' . strtoupper(Str::random(20)),
            'gateway_txn_id' => 'SIM-' . strtoupper(Str::random(20)),
            'test_mode' => $order->test_mode,
            'fee_amount' => 0,
            'net_amount' => $order->amount,
            'payment_details' => $order->payment_details,
            'customer_email' => $paymentData['customer_details']['email'] ?? null,
            'customer_phone' => $paymentData['customer_details']['phone'] ?? null,
            'processed_at' => now(),
            'captured_at' => $success ? now() : null,
        ]);

        return $transaction;
    }

    /**
     * Update payment link status
     *
     * @param int $paymentLinkId
     * @param bool $success
     * @return void
     */
    private function updatePaymentLink(int $paymentLinkId, bool $success): void
    {
        if ($success) {
            $paymentLink = PaymentLink::find($paymentLinkId);
            if ($paymentLink) {
                $paymentLink->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'usage_count' => $paymentLink->usage_count + 1,
                ]);
            }
        }
    }

    /**
     * Sanitize payment details (remove sensitive data)
     *
     * @param array $details
     * @return array
     */
    private function sanitizePaymentDetails(array $details): array
    {
        $sanitized = $details;

        // Mask card number if present
        if (isset($sanitized['card_number'])) {
            $cardNumber = preg_replace('/\s+/', '', $sanitized['card_number']);
            $sanitized['card_number'] = '****' . substr($cardNumber, -4);
        }

        // Remove CVV
        unset($sanitized['cvv']);

        return $sanitized;
    }

    /**
     * Get appropriate failure message
     *
     * @param array $paymentData
     * @return string
     */
    private function getFailureMessage(array $paymentData): string
    {
        $paymentMethod = $paymentData['payment_method'] ?? 'card';

        if ($paymentMethod === 'card' && isset($paymentData['payment_details']['card_number'])) {
            $cardNumber = preg_replace('/\s+/', '', $paymentData['payment_details']['card_number']);
            
            if ($cardNumber === '4000000000000002') {
                return 'Your card was declined. Please try another card.';
            }
            if ($cardNumber === '4000000000009995') {
                return 'Insufficient funds. Please use another payment method.';
            }
            if (in_array($cardNumber, ['4000000000009987', '4000000000009979'])) {
                return 'This card cannot be used. Please contact your bank.';
            }
        }

        $messages = [
            'Payment was declined by your bank.',
            'Transaction failed. Please try again.',
            'Unable to process payment at this time.',
            'Payment could not be completed.',
        ];

        return $messages[array_rand($messages)];
    }

    /**
     * Get test card information for documentation
     *
     * @return array
     */
    public static function getTestCards(): array
    {
        return [
            'success' => [
                ['number' => '4242 4242 4242 4242', 'brand' => 'Visa', 'result' => 'Always succeeds'],
                ['number' => '5555 5555 5555 4444', 'brand' => 'Mastercard', 'result' => 'Always succeeds'],
                ['number' => '3782 822463 10005', 'brand' => 'Amex', 'result' => 'Always succeeds'],
                ['number' => '6011 1111 1111 1117', 'brand' => 'Discover', 'result' => 'Always succeeds'],
            ],
            'failure' => [
                ['number' => '4000 0000 0000 0002', 'result' => 'Card declined'],
                ['number' => '4000 0000 0000 9995', 'result' => 'Insufficient funds'],
                ['number' => '4000 0000 0000 9987', 'result' => 'Lost card'],
                ['number' => '4000 0000 0000 9979', 'result' => 'Stolen card'],
            ],
        ];
    }
}

