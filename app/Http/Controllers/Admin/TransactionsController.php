<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\LogsConditionally;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TransactionsController extends Controller
{
    use LogsConditionally;

    public function index(): View
    {
        $this->logInfo('Admin transactions page accessed', ['user_id' => auth()->id()]);
        return view('admin.payments.transactions');
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 5), 50);
            
            $query = Transaction::with(['merchant', 'order'])->latest();

            // Date range filter
            if ($request->has('date_range') && $request->get('date_range')) {
                $dates = explode(' - ', $request->get('date_range'));
                if (count($dates) === 2) {
                    $query->whereBetween('created_at', [trim($dates[0]), trim($dates[1])]);
                }
            }

            // Status filter
            if ($request->has('status') && $request->get('status') !== 'all') {
                $query->where('status', $request->get('status'));
            }

            // Column filters
            if ($request->has('filter_merchant_id') && $request->get('filter_merchant_id')) {
                $query->where('merchant_id', $request->get('filter_merchant_id'));
            }
            if ($request->has('filter_merchant_name') && $request->get('filter_merchant_name')) {
                $query->whereHas('merchant', function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->get('filter_merchant_name')}%");
                });
            }
            if ($request->has('filter_transaction_id') && $request->get('filter_transaction_id')) {
                $query->where('txn_id', 'like', "%{$request->get('filter_transaction_id')}%");
            }
            if ($request->has('filter_order_id') && $request->get('filter_order_id')) {
                $query->where('order_id', 'like', "%{$request->get('filter_order_id')}%");
            }
            if ($request->has('filter_payment_status') && $request->get('filter_payment_status') !== 'all') {
                $query->where('status', $request->get('filter_payment_status'));
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortDirection = $request->get('sort_direction', 'desc');
            if (in_array($sortBy, ['id', 'txn_id', 'created_at', 'amount', 'status'])) {
                $query->orderBy($sortBy, $sortDirection);
            }

            $transactions = $query->paginate($perPage);

            // Format data with all required columns
            $data = $transactions->map(function($transaction) {
                $paymentDetails = $transaction->payment_details ?? [];
                $gatewayResponse = $transaction->gateway_response ?? [];
                
                return [
                    'id' => $transaction->id,
                    'merchant_id' => $transaction->merchant_id,
                    'transaction_initiation_time' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'merchant_name' => $transaction->merchant->name ?? '-',
                    'transaction_sequence_id' => $transaction->id,
                    'transaction_order_id' => $transaction->order_id ?? '-',
                    'transaction_datetime' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'transaction_id' => $transaction->txn_id,
                    'amount_paid_by_customer' => number_format($transaction->amount, 2),
                    'payment_status' => $transaction->status,
                    'payment_mode' => $transaction->payment_method ?? '-',
                    'payment_channel' => $gatewayResponse['channel'] ?? '-',
                    'merc_approved' => $transaction->status === 'success' ? 'Yes' : 'No',
                    'currency_code' => $transaction->currency ?? 'INR',
                    'bank_reference_number' => $gatewayResponse['bank_reference'] ?? '-',
                    'acq_payment_id' => $gatewayResponse['acq_payment_id'] ?? '-',
                    'acq_transaction_id' => $gatewayResponse['acq_transaction_id'] ?? '-',
                    'provider_name' => $gatewayResponse['provider'] ?? '-',
                    'account_id' => $gatewayResponse['account_id'] ?? '-',
                    'tdr_amount' => number_format($transaction->fee_amount ?? 0, 2),
                    'gst_amount' => number_format(($transaction->fee_amount ?? 0) * 0.18, 2),
                    'is_updated_by_recon' => 'No',
                    'tdr_amount_paid_by_merchant' => number_format($transaction->fee_amount ?? 0, 2),
                    'tdr_amount_paid_by_customer' => '0.00',
                    'gst_paid_by_merchant' => number_format(($transaction->fee_amount ?? 0) * 0.18, 2),
                    'gst_paid_by_customer' => '0.00',
                    'net_settlements_amount' => number_format($transaction->net_amount ?? $transaction->amount, 2),
                    'card_holder_name' => $paymentDetails['card_holder_name'] ?? '-',
                    'card_number' => isset($paymentDetails['card_number']) ? '****' . substr($paymentDetails['card_number'], -4) : '-',
                    'customer_ip_address' => $transaction->ip_address ?? '-',
                    'udf1' => $paymentDetails['udf1'] ?? '-',
                    'udf2' => $paymentDetails['udf2'] ?? '-',
                    'udf3' => $paymentDetails['udf3'] ?? '-',
                    'udf4' => $paymentDetails['udf4'] ?? '-',
                    'udf5' => $paymentDetails['udf5'] ?? '-',
                    'upi_id' => $gatewayResponse['upi_id'] ?? '-',
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
            $this->logError('Error fetching transactions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
            ], 500);
        }
    }
}


