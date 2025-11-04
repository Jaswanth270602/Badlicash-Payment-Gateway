<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\LogsConditionally;
use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    use LogsConditionally;

    public function index(): View
    {
        $this->logInfo('Admin dashboard accessed', ['user_id' => auth()->id()]);
        return view('admin.dashboard');
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $this->logInfo('Admin dashboard data requested', ['user_id' => auth()->id()]);
            
            $stats = [
                'total_merchants' => Merchant::count(),
                'active_merchants' => Merchant::where('status', 'active')->count(),
                'total_transactions' => Transaction::count(),
                'total_volume' => Transaction::where('status', 'success')->sum('amount'),
            ];

            // Recent activity (last 10 transactions)
            $recentActivity = Transaction::with('merchant')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'title' => 'Transaction ' . $transaction->txn_id,
                        'description' => 'Merchant: ' . ($transaction->merchant->name ?? 'N/A') . ' - ' . $transaction->currency . ' ' . number_format($transaction->amount, 2),
                        'created_at' => $transaction->created_at->toISOString(),
                    ];
                });

            $this->logInfo('Admin dashboard data retrieved successfully', [
                'stats' => $stats,
                'activity_count' => $recentActivity->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_activity' => $recentActivity,
                ],
            ]);
        } catch (\Exception $e) {
            $this->logError('Error fetching admin dashboard data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
            ], 500);
        }
    }
}

