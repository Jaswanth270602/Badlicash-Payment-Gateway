<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Traits\LogsConditionally;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionsController extends Controller
{
    use LogsConditionally;
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

        // Filter by current merchant mode (test or live)
        $query = $merchant->transactions()
            ->where('test_mode', $merchant->test_mode)
            ->with(['order.paymentLink'])
            ->latest();

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
        $this->logInfo('Admin transactions page accessed', ['user_id' => auth()->id()]);
        return view('admin.transactions.index');
    }

    /**
     * Get all transactions data for admin.
     */
    public function getDataAdmin(Request $request): JsonResponse
    {
        try {
            $this->logInfo('Admin transactions data requested', [
                'user_id' => auth()->id(),
                'filters' => $request->only(['merchant_id', 'status', 'per_page'])
            ]);

            $perPage = min($request->get('per_page', 10), 50);
            $merchantId = $request->get('merchant_id');
            $status = $request->get('status');

            $query = \App\Models\Transaction::with(['order.paymentLink', 'merchant'])->latest();

            if ($merchantId) {
                $query->where('merchant_id', $merchantId);
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $transactions = $query->paginate($perPage);

            $this->logDebug('Admin transactions retrieved', [
                'count' => $transactions->count(),
                'total' => $transactions->total()
            ]);

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
        } catch (\Exception $e) {
            $this->logError('Error fetching admin transactions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
            ], 500);
        }
    }

    public function export(Request $request): StreamedResponse
    {
        $merchant = $request->user()->merchant;

        $query = $merchant->transactions()
            ->where('test_mode', $merchant->test_mode)
            ->with(['order.paymentLink']);

        $status = $request->get('status');
        $paymentMethod = $request->get('payment_method');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $search = $request->get('search');

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

        $transactions = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Transaction ID', 'Order ID', 'Amount', 'Fee Amount', 'Net Amount',
                'Currency', 'Payment Method', 'Status', 'Customer Email', 'Customer Phone',
                'Created At', 'Failure Reason'
            ]);

            foreach ($transactions as $transaction) {
                $paymentDetails = $transaction->payment_details ?? [];
                fputcsv($file, [
                    $transaction->txn_id,
                    $transaction->order_id ?? '-',
                    number_format($transaction->amount, 2),
                    number_format($transaction->fee_amount ?? 0, 2),
                    number_format($transaction->net_amount ?? $transaction->amount, 2),
                    $transaction->currency ?? 'INR',
                    $transaction->payment_method ?? '-',
                    $transaction->status,
                    $paymentDetails['customer_email'] ?? '-',
                    $paymentDetails['customer_phone'] ?? '-',
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->failure_reason ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

