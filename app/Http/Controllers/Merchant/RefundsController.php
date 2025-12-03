<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RefundsController extends Controller
{
    protected RefundService $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    public function index(): View
    {
        return view('merchant.refunds.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;
        $perPage = min($request->get('per_page', 10), 100);
        
        $status = $request->get('status');
        $search = $request->get('search');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        // Filter by current merchant mode through transaction relationship
        $query = $merchant->refunds()
            ->with('transaction')
            ->whereHas('transaction', function ($q) use ($merchant) {
                $q->where('test_mode', $merchant->test_mode);
            })
            ->latest();

        if ($status && $status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('refund_id', 'like', "%{$search}%")
                  ->orWhereHas('transaction', function ($tq) use ($search) {
                      $tq->where('txn_id', 'like', "%{$search}%");
                  });
            });
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $refunds = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $refunds->items(),
            'pagination' => [
                'current_page' => $refunds->currentPage(),
                'per_page' => $refunds->perPage(),
                'total' => $refunds->total(),
                'last_page' => $refunds->lastPage(),
                'from' => $refunds->firstItem(),
                'to' => $refunds->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Refund creation via web interface
        try {
            $merchant = $request->user()->merchant;
            
            // Check if merchant is in LIVE mode without proper credentials
            if (!$merchant->test_mode && !$merchant->hasLiveCredentials()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Live mode is not configured. Please configure your live API credentials before processing refunds in LIVE mode.',
                    'error_code' => 'LIVE_MODE_NOT_CONFIGURED',
                ], 403);
            }
            
            // Validate input
            $request->validate([
                'transaction_id' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'reason' => 'nullable|string|max:500',
            ]);
            
            // Find transaction by txn_id (not database id)
            $transaction = $merchant->transactions()
                ->where('txn_id', $request->transaction_id)
                ->where('test_mode', $merchant->test_mode)
                ->firstOrFail();

            // Validate transaction status
            if ($transaction->status !== 'success') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot refund unsuccessful transaction. Only successful transactions can be refunded.',
                ], 400);
            }

            $refund = $this->refundService->createRefund(
                $transaction,
                $request->amount,
                $request->user(),
                $request->reason
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund created successfully',
                'data' => [
                    'refund_id' => $refund->refund_id,
                    'transaction_id' => $transaction->txn_id,
                    'amount' => $refund->amount,
                    'currency' => $refund->currency,
                    'status' => $refund->status,
                    'is_partial' => $refund->is_partial,
                    'created_at' => $refund->created_at->toIso8601String(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found. Please check the transaction ID and ensure you are in the correct mode (TEST/LIVE).',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

