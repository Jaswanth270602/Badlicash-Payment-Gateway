<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle.api');
    }

    /**
     * Get all orders for the merchant.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $merchant = $request->get('api_merchant');

        $perPage = min($request->get('per_page', 10), config('badlicash.pagination.max_per_page'));
        $status = $request->get('status');
        $testMode = $request->get('test_mode');

        $query = $merchant->orders()->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($testMode !== null) {
            $query->where('test_mode', filter_var($testMode, FILTER_VALIDATE_BOOLEAN));
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Get a specific order.
     *
     * @param Request $request
     * @param string $orderId
     * @return JsonResponse
     */
    public function show(Request $request, string $orderId): JsonResponse
    {
        $merchant = $request->get('api_merchant');

        $order = $merchant->orders()
            ->where('order_id', $orderId)
            ->with('transactions')
            ->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->order_id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'status' => $order->status,
                'customer_details' => $order->customer_details,
                'description' => $order->description,
                'test_mode' => $order->test_mode,
                'transactions' => $order->transactions->map(function ($txn) {
                    return [
                        'transaction_id' => $txn->txn_id,
                        'payment_method' => $txn->payment_method,
                        'amount' => $txn->amount,
                        'status' => $txn->status,
                        'created_at' => $txn->created_at->toIso8601String(),
                    ];
                }),
                'created_at' => $order->created_at->toIso8601String(),
                'updated_at' => $order->updated_at->toIso8601String(),
            ],
        ]);
    }
}

