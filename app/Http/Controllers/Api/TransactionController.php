<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle.api');
    }

    /**
     * Get all transactions for the merchant.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $merchant = $request->get('api_merchant');

        $perPage = min($request->get('per_page', 10), config('badlicash.pagination.max_per_page'));
        $status = $request->get('status');
        $paymentMethod = $request->get('payment_method');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = $merchant->transactions()->with('order')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    /**
     * Get a specific transaction.
     *
     * @param Request $request
     * @param string $transactionId
     * @return JsonResponse
     */
    public function show(Request $request, string $transactionId): JsonResponse
    {
        $merchant = $request->get('api_merchant');

        $transaction = $merchant->transactions()
            ->where('txn_id', $transactionId)
            ->with('order', 'refunds')
            ->first();

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transaction_id' => $transaction->txn_id,
                'order_id' => $transaction->order->order_id,
                'amount' => $transaction->amount,
                'fee_amount' => $transaction->fee_amount,
                'net_amount' => $transaction->net_amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'refunds' => $transaction->refunds->map(function ($refund) {
                    return [
                        'refund_id' => $refund->refund_id,
                        'amount' => $refund->amount,
                        'status' => $refund->status,
                        'created_at' => $refund->created_at->toIso8601String(),
                    ];
                }),
                'created_at' => $transaction->created_at->toIso8601String(),
            ],
        ]);
    }
}

