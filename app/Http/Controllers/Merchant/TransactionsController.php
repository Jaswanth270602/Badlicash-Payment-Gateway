<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransactionsController extends Controller
{
    /**
     * Display transactions page.
     */
    public function index(): View
    {
        return view('merchant.transactions.index');
    }

    /**
     * Get transactions data for Angular.
     */
    public function getData(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        $perPage = min($request->get('per_page', 10), 100);
        $status = $request->get('status');
        $paymentMethod = $request->get('payment_method');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search');

        $query = $merchant->transactions()->with('order')->latest();

        if ($status && $status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }

        if ($paymentMethod && $paymentMethod !== 'all' && $paymentMethod !== '') {
            $query->where('payment_method', $paymentMethod);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('txn_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($oq) use ($search) {
                      $oq->where('order_id', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                  });
            });
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
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
        ]);
    }

    /**
     * Admin view for all transactions.
     */
    public function indexAdmin(): View
    {
        return view('admin.transactions.index');
    }

    /**
     * Get all transactions data for admin.
     */
    public function getDataAdmin(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 10), 50);
        $merchantId = $request->get('merchant_id');
        $status = $request->get('status');

        $query = \App\Models\Transaction::with('order', 'merchant')->latest();

        if ($merchantId) {
            $query->where('merchant_id', $merchantId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
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
}

