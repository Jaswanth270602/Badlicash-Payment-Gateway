<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Traits\LogsConditionally;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SplitTransactionsController extends Controller
{
    use LogsConditionally;

    public function index(): View
    {
        return view('merchant.payments.split-transactions');
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $merchant = $request->user()->merchant;
            $perPage = min($request->get('per_page', 5), 50);
            
            $query = $merchant->transactions()
                ->where('test_mode', $merchant->test_mode)
                ->whereNotNull('order_id')
                ->latest();

            // Date range filter
            if ($request->has('date_range') && $request->get('date_range')) {
                $dates = explode(' - ', $request->get('date_range'));
                if (count($dates) === 2) {
                    $query->whereBetween('created_at', [trim($dates[0]), trim($dates[1])]);
                }
            }

            // Column filters
            if ($request->has('filter_transaction_id') && $request->get('filter_transaction_id')) {
                $query->where('txn_id', 'like', "%{$request->get('filter_transaction_id')}%");
            }
            if ($request->has('filter_order_id') && $request->get('filter_order_id')) {
                $query->where('order_id', 'like', "%{$request->get('filter_order_id')}%");
            }

            $transactions = $query->paginate($perPage);

            // Format data for split transactions
            $data = $transactions->map(function($transaction) {
                $paymentDetails = $transaction->payment_details ?? [];
                return [
                    'id' => $transaction->id,
                    'transaction_date' => $transaction->created_at->format('Y-m-d'),
                    'merchant_id' => $transaction->merchant_id,
                    'merchant_name' => $transaction->merchant->name ?? '-',
                    'msac_code' => '-',
                    'tran_id' => $transaction->txn_id,
                    'transaction_id' => $transaction->txn_id,
                    'order_id' => $transaction->order_id ?? '-',
                    'amount_paid_by_customer' => number_format($transaction->amount, 2),
                    'account' => '-',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch split transactions',
            ], 500);
        }
    }
}

