<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MerchantsController extends Controller
{
    public function index(): View
    {
        return view('admin.merchants.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 10), 50);

        $merchants = \App\Models\Merchant::latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $merchants->items(),
            'pagination' => [
                'current_page' => $merchants->currentPage(),
                'per_page' => $merchants->perPage(),
                'total' => $merchants->total(),
                'last_page' => $merchants->lastPage(),
            ],
        ]);
    }
}

