<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_merchants' => \App\Models\Merchant::count(),
            'active_merchants' => \App\Models\Merchant::where('status', 'active')->count(),
            'total_transactions' => \App\Models\Transaction::count(),
            'total_volume' => \App\Models\Transaction::where('status', 'success')->sum('amount'),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

