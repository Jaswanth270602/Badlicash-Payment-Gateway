<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\Merchant;
use App\Services\BankProviders\BankProviderInterface;
use App\Events\PaymentCreated;
use App\Events\PaymentSuccess;
use App\Events\PaymentFailed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected BankProviderInterface $bankProvider;

    public function __construct(BankProviderInterface $bankProvider)
    {
        $this->bankProvider = $bankProvider;
    }

    /**
     * Create a new payment order.
     */
    public function createOrder(Merchant $merchant, array $data): Order
    {
        // Check for idempotency
        if (isset($data['idempotency_key'])) {
            $existingOrder = Order::where('idempotency_key', $data['idempotency_key'])
                ->where('merchant_id', $merchant->id)
                ->first();

            if ($existingOrder) {
                return $existingOrder;
            }
        }

        $order = Order::create([
            'merchant_id' => $merchant->id,
            'order_id' => Order::generateOrderId(),
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? $merchant->default_currency,
            'customer_details' => $data['customer_details'] ?? null,
            'status' => 'created',
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'return_url' => $data['return_url'] ?? null,
            'cancel_url' => $data['cancel_url'] ?? null,
            'idempotency_key' => $data['idempotency_key'] ?? null,
            'test_mode' => $merchant->test_mode,
            'expires_at' => isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : now()->addHours(24),
        ]);

        event(new PaymentCreated($order));

        return $order;
    }

    /**
     * Process a payment for an order.
     */
    public function processPayment(Order $order, array $paymentData): Transaction
    {
        return DB::transaction(function () use ($order, $paymentData) {
            // Get the appropriate bank provider based on merchant mode
            $bankProvider = $this->getBankProvider($order->merchant);
            
            // Check for idempotency
            if (isset($paymentData['idempotency_key'])) {
                $existingTransaction = Transaction::where('idempotency_key', $paymentData['idempotency_key'])
                    ->where('order_id', $order->id)
                    ->first();

                if ($existingTransaction) {
                    return $existingTransaction;
                }
            }

            // Create transaction record
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'merchant_id' => $order->merchant_id,
                'txn_id' => Transaction::generateTxnId(),
                'payment_method' => $paymentData['payment_method'],
                'amount' => $order->amount,
                'fee_amount' => $order->merchant->calculateFee($order->amount),
                'net_amount' => 0, // Will be calculated
                'currency' => $order->currency,
                'status' => 'initiated',
                'payment_details' => $this->sanitizePaymentDetails($paymentData),
                'test_mode' => $order->test_mode,
                'idempotency_key' => $paymentData['idempotency_key'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Calculate net amount
            $transaction->net_amount = $transaction->calculateNetAmount();
            $transaction->save();

            // Process payment through bank provider
            try {
                $result = $bankProvider->processPayment([
                    'merchant_id' => $order->merchant_id,
                    'order_id' => $order->order_id,
                    'transaction_id' => $transaction->txn_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'payment_method' => $paymentData['payment_method'],
                    'payment_details' => $paymentData,
                ]);

                if ($result['success']) {
                    $transaction->update([
                        'status' => 'success',
                        'gateway_response' => $result,
                        'gateway_txn_id' => $result['gateway_txn_id'] ?? null,
                        'payment_details' => array_merge(
                            $transaction->payment_details ?? [],
                            $result['payment_details'] ?? []
                        ),
                        'captured_at' => now(),
                    ]);

                    $order->update(['status' => 'completed']);
                    event(new PaymentSuccess($transaction));
                } else {
                    $transaction->update([
                        'status' => 'failed',
                        'gateway_response' => $result,
                    ]);

                    $order->update(['status' => 'failed']);
                    event(new PaymentFailed($transaction));
                }

            } catch (\Exception $e) {
                Log::error('Payment processing error', [
                    'transaction_id' => $transaction->txn_id,
                    'error' => $e->getMessage(),
                ]);

                $transaction->update([
                    'status' => 'failed',
                    'gateway_response' => [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ]);

                $order->update(['status' => 'failed']);
                event(new PaymentFailed($transaction));
            }

            return $transaction;
        });
    }

    /**
     * Sanitize payment details to remove sensitive information.
     */
    protected function sanitizePaymentDetails(array $paymentData): array
    {
        // Remove full card number, CVV, etc.
        $sanitized = $paymentData;

        // Remove sensitive fields
        unset($sanitized['card_number'], $sanitized['cvv'], $sanitized['pin']);

        // Keep only last 4 digits if card number was provided
        if (isset($paymentData['card_number'])) {
            $sanitized['last4'] = substr($paymentData['card_number'], -4);
        }

        return $sanitized;
    }

    /**
     * Get the appropriate bank provider for the merchant.
     */
    protected function getBankProvider(Merchant $merchant): BankProviderInterface
    {
        if ($merchant->test_mode) {
            return new \App\Services\BankProviders\SandboxBankProvider();
        }

        // For production, try to get merchant-specific API credentials
        $apiKey = $merchant->settings['production_api_key'] ?? null;
        $apiSecret = $merchant->settings['production_api_secret'] ?? null;
        $bankName = $merchant->settings['production_bank_name'] ?? null;

        return new \App\Services\BankProviders\ProductionBankProvider($apiKey, $apiSecret, $bankName);
    }

    /**
     * Verify payment status.
     */
    public function verifyPayment(Transaction $transaction): array
    {
        $bankProvider = $this->getBankProvider($transaction->merchant);
        $result = $bankProvider->verifyPayment($transaction->txn_id);

        if ($result['verified'] && $transaction->status !== 'success') {
            $transaction->update([
                'status' => 'success',
                'captured_at' => now(),
            ]);

            $transaction->order->update(['status' => 'completed']);
            event(new PaymentSuccess($transaction));
        }

        return $result;
    }
}

