<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('throttle.api');
    }

    /**
     * Create a new payment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
            'payment_method' => 'required|in:card,netbanking,upi,wallet,emi',
            'customer_details' => 'nullable|array',
            'customer_details.name' => 'nullable|string',
            'customer_details.email' => 'nullable|email',
            'customer_details.phone' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
            'return_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
            'idempotency_key' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        try {
            $merchant = $request->get('api_merchant');

            // Create order
            $order = $this->paymentService->createOrder($merchant, $validator->validated());

            // Process payment
            $transaction = $this->paymentService->processPayment($order, $validator->validated());

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->order_id,
                    'transaction_id' => $transaction->txn_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'created_at' => $transaction->created_at->toIso8601String(),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Payment creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify a payment.
     *
     * @param Request $request
     * @param string $transactionId
     * @return JsonResponse
     */
    public function verifyPayment(Request $request, string $transactionId): JsonResponse
    {
        $merchant = $request->get('api_merchant');

        $transaction = $merchant->transactions()
            ->where('txn_id', $transactionId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found',
            ], 404);
        }

        try {
            $result = $this->paymentService->verifyPayment($transaction);

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->txn_id,
                    'status' => $transaction->status,
                    'verified' => $result['verified'] ?? false,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Verification failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

