<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\PaymentLink;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class PaymentLinksController extends Controller
{
    /**
     * Display payment links page.
     */
    public function index(): View
    {
        return view('merchant.paymentlinks.index');
    }

    /**
     * Get payment links data for Angular.
     */
    public function getData(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        $perPage = min($request->get('per_page', 10), 50);
        $status = $request->get('status');
        $search = $request->get('search');

        $query = $merchant->paymentLinks()->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('link_token', 'like', "%{$search}%");
            });
        }

        $paymentLinks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paymentLinks->items(),
            'pagination' => [
                'current_page' => $paymentLinks->currentPage(),
                'per_page' => $paymentLinks->perPage(),
                'total' => $paymentLinks->total(),
                'last_page' => $paymentLinks->lastPage(),
                'from' => $paymentLinks->firstItem(),
                'to' => $paymentLinks->lastItem(),
            ],
        ]);
    }

    /**
     * Store a new payment link.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
            'expires_in_hours' => 'nullable|integer|min:1|max:720', // max 30 days
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*' => 'in:card,upi,netbanking,wallet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $merchant = $request->user()->merchant;

            $expiresAt = isset($request->expires_in_hours) 
                ? now()->addHours($request->expires_in_hours) 
                : now()->addHours(24);

            $paymentLink = PaymentLink::create([
                'merchant_id' => $merchant->id,
                'link_token' => PaymentLink::generateLinkToken(),
                'title' => $request->title,
                'description' => $request->description,
                'amount' => $request->amount,
                'currency' => $request->currency ?? $merchant->default_currency ?? 'INR',
                'status' => 'active',
                'test_mode' => $merchant->test_mode,
                'payment_methods' => $request->payment_methods ?? ['card', 'upi', 'netbanking', 'wallet'],
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment link created successfully',
                'data' => $paymentLink,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment link: ' . $e->getMessage(),
            ], 500);
        }
    }
}

