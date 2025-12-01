<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return view('admin.dashboard', [
                'user' => $user,
            ]);
        }

        if ($user->isMerchant()) {
            $merchant = $user->merchant;

            // Get statistics
            $stats = [
                'total_transactions' => $merchant->transactions()->count(),
                'successful_transactions' => $merchant->transactions()->where('status', 'success')->count(),
                'total_volume' => $merchant->transactions()->where('status', 'success')->sum('amount'),
                'pending_refunds' => $merchant->refunds()->where('status', 'pending')->count(),
            ];

            return view('merchant.dashboard', [
                'user' => $user,
                'merchant' => $merchant,
                'stats' => $stats,
            ]);
        }

        return view('dashboard', ['user' => $user]);
    }
}

