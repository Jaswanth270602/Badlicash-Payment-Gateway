<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        return view('merchant.reports.index');
    }

    public function getData(Request $request): \Illuminate\Http\JsonResponse
    {
        $merchant = $request->user()->merchant;
        
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = $merchant->transactions();

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $transactions = $query->get();

        $totalAmount = $transactions->where('status', 'success')->sum('amount');
        $successful = $transactions->where('status', 'success')->count();
        $failed = $transactions->where('status', 'failed')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_transactions' => $transactions->count(),
                'total_amount' => $totalAmount,
                'successful' => $successful,
                'failed' => $failed,
            ],
        ]);
    }

    public function export(Request $request): Response
    {
        $merchant = $request->user()->merchant;

        // Generate CSV
        $transactions = $merchant->transactions()
            ->when($request->from_date, fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->get();

        $csv = $this->generateCsv($transactions);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="transactions_' . now()->format('Y-m-d') . '.csv"');
    }

    public function indexAdmin(): View
    {
        return view('admin.reports.index');
    }

    public function exportAdmin(Request $request): Response
    {
        // Generate CSV for all merchants
        $transactions = \App\Models\Transaction::query()
            ->when($request->merchant_id, fn($q) => $q->where('merchant_id', $request->merchant_id))
            ->when($request->from_date, fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->get();

        $csv = $this->generateCsv($transactions);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="admin_transactions_' . now()->format('Y-m-d') . '.csv"');
    }

    protected function generateCsv($transactions): string
    {
        $output = fopen('php://temp', 'r+');

        // Header row
        fputcsv($output, [
            'Transaction ID',
            'Order ID',
            'Amount',
            'Fee',
            'Net Amount',
            'Currency',
            'Payment Method',
            'Status',
            'Created At',
        ]);

        // Data rows
        foreach ($transactions as $txn) {
            fputcsv($output, [
                $txn->txn_id,
                $txn->order->order_id ?? '',
                $txn->amount,
                $txn->fee_amount,
                $txn->net_amount,
                $txn->currency,
                $txn->payment_method,
                $txn->status,
                $txn->created_at->toDateTimeString(),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}

