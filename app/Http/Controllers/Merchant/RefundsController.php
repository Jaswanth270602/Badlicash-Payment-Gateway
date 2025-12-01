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

        $query = $merchant->refunds()->with('transaction')->latest();

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
            $transaction = $request->user()->merchant->transactions()
                ->where('id', $request->transaction_id)
                ->firstOrFail();

            $refund = $this->refundService->createRefund(
                $transaction,
                $request->amount,
                $request->user(),
                $request->reason
            );

            return response()->json([
                'success' => true,
                'data' => $refund,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

