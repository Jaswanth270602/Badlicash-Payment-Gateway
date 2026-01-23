<?php

namespace App\Http\Controllers;

use App\Models\PaymentLink;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Acquirers\AcquirerResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * CashFree Webhook Controller
 * 
 * Handles CashFree payment webhooks for order status updates.
 */
class CashFreeWebhookController extends Controller
{
    protected $acquirerResolver;

    public function __construct(AcquirerResolver $acquirerResolver)
    {
        $this->acquirerResolver = $acquirerResolver;
    }

    /**
     * Handle CashFree webhook for payment link token.
     */
    public function handleWebhook(Request $request, string $token)
    {
        try {
            Log::info('CashFree webhook received', [
                'token' => $token,
                'payload' => $request->all(),
            ]);

            // Find payment link by token
            $paymentLink = PaymentLink::where('link_token', $token)->first();

            if (!$paymentLink) {
                Log::warning('CashFree webhook: Payment link not found', ['token' => $token]);
                return response()->json(['error' => 'Payment link not found'], 404);
            }

            // Get merchant's CashFree acquirer account
            $acquirerAccount = $paymentLink->merchant->acquirerAccounts()
                ->where('acquirer_name', 'cashfree')
                ->where('is_active', true)
                ->first();

            if (!$acquirerAccount) {
                Log::warning('CashFree webhook: Acquirer account not found', [
                    'merchant_id' => $paymentLink->merchant_id,
                ]);
                return response()->json(['error' => 'Acquirer account not found'], 404);
            }

            // Resolve CashFree adapter
            $adapter = $this->acquirerResolver->resolve($acquirerAccount);

            // Verify webhook signature
            // CashFree sends signature in x-webhook-signature header
            $signature = $request->header('x-webhook-signature') ?? $request->header('x-cashfree-signature') ?? '';
            $payload = $request->all();

            if (empty($signature)) {
                Log::warning('CashFree webhook: Missing signature header', [
                    'token' => $token,
                    'headers' => $request->headers->all(),
                ]);
                // In test mode, allow webhooks without signature for development
                if (config('app.env') !== 'production') {
                    Log::info('CashFree webhook: Allowing without signature in non-production mode');
                } else {
                    return response()->json(['error' => 'Missing signature'], 401);
                }
            } else {
                if (!$adapter->verifyWebhookSignature($payload, $signature)) {
                    Log::warning('CashFree webhook: Signature verification failed', [
                        'token' => $token,
                        'merchant_id' => $paymentLink->merchant_id,
                    ]);
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }

            // Extract order and payment information from webhook
            // CashFree webhook payload structure:
            // - orderId, orderAmount, referenceId, txStatus, paymentMode, txMsg, txTime
            $orderId = $payload['orderId'] ?? $payload['order']['order_id'] ?? $payload['data']['orderId'] ?? null;
            $paymentId = $payload['referenceId'] ?? $payload['cf_payment_id'] ?? $payload['payment']['cf_payment_id'] ?? null;
            $paymentStatus = $payload['txStatus'] ?? $payload['payment_status'] ?? $payload['payment']['payment_status'] ?? null;
            $orderStatus = $payload['orderStatus'] ?? $payload['order']['order_status'] ?? $payload['data']['order']['order_status'] ?? null;

            if (!$orderId) {
                Log::warning('CashFree webhook: Order ID missing', [
                    'token' => $token,
                    'payload' => $payload,
                ]);
                return response()->json(['error' => 'Order ID missing'], 400);
            }

            // Find order by gateway_order_id
            $order = Order::where('gateway_order_id', $orderId)
                ->where('merchant_id', $paymentLink->merchant_id)
                ->first();

            if (!$order) {
                Log::warning('CashFree webhook: Order not found', [
                    'gateway_order_id' => $orderId,
                    'merchant_id' => $paymentLink->merchant_id,
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Get transaction
            $transaction = $order->transactions()->first();

            if (!$transaction) {
                Log::warning('CashFree webhook: Transaction not found', [
                    'order_id' => $order->id,
                ]);
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Normalize status using adapter
            // CashFree uses: ACTIVE (pending), PAID (success), EXPIRED/CANCELLED (failed)
            $rawStatus = $paymentStatus ?? $orderStatus ?? $payload['txStatus'] ?? 'ACTIVE';
            $normalizedStatus = $adapter->normalizeStatus($rawStatus);
            
            Log::info('CashFree webhook: Status normalization', [
                'raw_status' => $rawStatus,
                'normalized_status' => $normalizedStatus,
                'order_id' => $orderId,
            ]);

            // Update transaction
            DB::transaction(function () use ($transaction, $paymentId, $normalizedStatus, $order, $paymentLink) {
                $transaction->gateway_txn_id = $paymentId ?? $transaction->gateway_txn_id;
                $transaction->gateway_transaction_id = $paymentId ?? $transaction->gateway_transaction_id;
                $transaction->status = $normalizedStatus;
                $transaction->save();

                // Update order status
                $order->status = $normalizedStatus === 'success' ? 'completed' : ($normalizedStatus === 'failed' ? 'failed' : 'pending');
                $order->save();

                // Update payment link if successful
                if ($normalizedStatus === 'success') {
                    if ($paymentLink->allow_partial_payment) {
                        $paymentLink->addPartialPayment($transaction->amount);
                    } else {
                        $paymentLink->markAsPaid();
                    }
                }
            });

            Log::info('CashFree webhook processed successfully', [
                'order_id' => $order->order_id,
                'transaction_id' => $transaction->txn_id,
                'status' => $normalizedStatus,
                'payment_id' => $paymentId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('CashFree webhook exception', [
                'token' => $token,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Webhook processing failed',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Verify CashFree order status (for polling/verification).
     */
    public function verifyOrder(Request $request, string $token)
    {
        try {
            $gatewayOrderId = $request->input('gateway_order_id') ?? $request->input('order_id');

            if (!$gatewayOrderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gateway order ID is required',
                ], 400);
            }

            // Find payment link
            $paymentLink = PaymentLink::where('link_token', $token)->first();

            if (!$paymentLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment link not found',
                ], 404);
            }

            // Get CashFree adapter
            $acquirerAccount = $paymentLink->merchant->acquirerAccounts()
                ->where('acquirer_name', 'cashfree')
                ->where('is_active', true)
                ->first();

            if (!$acquirerAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'CashFree acquirer account not found',
                ], 404);
            }

            $adapter = $this->acquirerResolver->resolve($acquirerAccount);

            // Get payment status from CashFree
            $statusResult = $adapter->getPaymentStatus($gatewayOrderId);

            if (!$statusResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $statusResult['message'] ?? 'Failed to get payment status',
                ], 400);
            }

            // Find and update transaction
            $order = Order::where('gateway_order_id', $gatewayOrderId)
                ->where('merchant_id', $paymentLink->merchant_id)
                ->first();

            if ($order) {
                $transaction = $order->transactions()->first();
                if ($transaction) {
                    $transaction->status = $statusResult['status'];
                    if (isset($statusResult['payment_id'])) {
                        $transaction->gateway_txn_id = $statusResult['payment_id'];
                        $transaction->gateway_transaction_id = $statusResult['payment_id'];
                    }
                    $transaction->save();

                    // Update payment link if successful
                    if ($statusResult['status'] === 'success') {
                        if ($paymentLink->allow_partial_payment) {
                            $paymentLink->addPartialPayment($transaction->amount);
                        } else {
                            $paymentLink->markAsPaid();
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'status' => $statusResult['status'],
                'payment_id' => $statusResult['payment_id'] ?? null,
                'order_id' => $order->order_id ?? null,
                'transaction_id' => $transaction->txn_id ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('CashFree order verification exception', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed',
            ], 500);
        }
    }
}

